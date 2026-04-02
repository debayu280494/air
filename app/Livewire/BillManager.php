<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Bill;
use App\Models\Customer;

class BillManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $groups = [];

    public $search = '';
    public $filterStatus = '';
    public $filterMonth = '';
    public $filterYear = '';
    public $filterGroup = '';

    // PAYMENT
    public $selectedBill;
    public $payment_method;
    public $paid_at;
    public $showPaymentModal = false;

    public function mount()
    {
        $this->groups = Customer::query()
            ->whereNotNull('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');
    }

    // reset page saat filter berubah
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterMonth()
    {
        $this->resetPage();
    }

    public function updatedFilterYear()
    {
        $this->resetPage();
    }

    public function updatedFilterGroup()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function openPayment($id)
    {
        $bill = Bill::with('customer')->find($id);
        if (!$bill) return;

        $this->selectedBill = $bill;
        $this->payment_method = 'cash';
        $this->paid_at = date('Y-m-d');
        $this->showPaymentModal = true;
    }

    public function processPayment()
    {
        $this->validate([
            'payment_method' => 'required',
            'paid_at' => 'required|date'
        ]);

        $bill = Bill::find($this->selectedBill->id);

        if (!$bill) {
            $this->dispatch('notify', type: 'error', message: 'Data tagihan tidak ditemukan');
            return;
        }

        $bill->update([
            'status' => 'lunas',
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
        ]);

        $this->reset(['selectedBill', 'payment_method', 'paid_at', 'showPaymentModal']);

        $this->dispatch('notify', type: 'success', message: 'Pembayaran berhasil');
    }

    public function deleteBill($id)
    {
        $bill = Bill::with('usage')->find($id);

        if (!$bill) return;

        if ($bill->status === 'lunas') {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Tidak bisa hapus, sudah lunas'
            ]);
            return;
        }

        $latest = Bill::where('customer_id', $bill->customer_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (!$latest || $latest->id !== $bill->id) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Hanya bulan terbaru yang bisa dihapus'
            ]);
            return;
        }

        $bill->usage?->delete();
        $bill->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Data berhasil dihapus'
        ]);
    }

    public function getBillsQuery()
    {
        return Bill::with('customer')
            ->when($this->search, function ($q) {
                $q->whereHas('customer', function ($c) {
                    $c->where('name', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterGroup, function ($q) {
                $q->whereHas('customer', function ($c) {
                    $c->where('group_name', $this->filterGroup);
                });
            })
            ->when($this->filterStatus !== '', function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterMonth !== '', function ($q) {
                $q->where('month', (int) $this->filterMonth);
            })
            ->when($this->filterYear !== '', function ($q) {
                $q->where('year', (int) $this->filterYear);
            });
    }

    public function render()
    {
        $bills = $this->getBillsQuery()
            ->orderByDesc('id')
            ->paginate(10);

        $latestIds = Bill::selectRaw('MAX(id) as id')
            ->groupBy('customer_id')
            ->pluck('id')
            ->toArray();

        $latestMap = array_flip($latestIds);

        foreach ($bills as $bill) {
            $bill->is_latest = isset($latestMap[$bill->id]);
        }

        return view('livewire.bill-manager', compact('bills'));
    }
}