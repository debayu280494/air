<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Bill;
use App\Models\Usage;
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

    // 🔥 PAYMENT
    public $selectedBill;
    public $payment_method;
    public $paid_at;
    public $showPaymentModal = false;

    public $totalTagihan;
    public $totalLunas;
    public $totalBelum;

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }

    public function mount()
    {
        $this->groups = Customer::select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');
    }

    // 🔥 OPEN MODAL
    public function openPayment($id)
    {
        $bill = Bill::find($id);

        if (!$bill) return;

        $this->selectedBill = $bill;
        $this->payment_method = 'cash';
        $this->paid_at = date('Y-m-d');

        $this->showPaymentModal = true;
    }

    // 🔥 PROCESS PAYMENT
    public function processPayment()
    {
        if (!$this->selectedBill) return;

        $this->validate([
            'payment_method' => 'required',
            'paid_at' => 'required|date'
        ]);

        $this->selectedBill->update([
            'status' => 'lunas',
            'payment_method' => $this->payment_method,
            'paid_at' => $this->paid_at,
        ]);

        $this->showPaymentModal = false;
    }

    public function deleteBill($id)
    {
        $bill = Bill::find($id);

        if (!$bill) return;

        // ❗ hanya boleh jika belum lunas
        if ($bill->status === 'lunas') {
            $this->addError('delete', 'Tidak bisa hapus, sudah lunas!');
            return;
        }

        // ❗ cek apakah ini data terbaru
        $latest = Bill::where('customer_id', $bill->customer_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (!$latest || $latest->id !== $bill->id) {
            $this->addError('delete', 'Hanya bulan terbaru yang bisa dihapus!');
            return;
        }

        // hapus usage dulu (kalau ada)
        if ($bill->usage_id) {
            Usage::where('id', $bill->usage_id)->delete();
        }

        $bill->delete();

        session()->flash('success', 'Data berhasil dihapus');
    }

    public function render()
    {
        $billsQuery = Bill::with('customer')
            ->when($this->search, function ($q) {
                $q->whereHas('customer', function ($c) {
                    $c->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterGroup, function ($q) {
                $q->whereHas('customer', function ($c) {
                    $c->where('group_name', $this->filterGroup);
                });
            })
            ->when($this->filterStatus, function ($q) {
                $q->where('status', $this->filterStatus);
            })
            ->when($this->filterMonth, function ($q) {
                $q->where('month', $this->filterMonth);
            })
            ->when($this->filterYear, function ($q) {
                $q->where('year', $this->filterYear);
            });

        $bills = $billsQuery
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(10);

        // ambil semua bill (untuk hitung latest)
        $allBills = $billsQuery->get();

        $latestPerCustomer = [];

        foreach ($allBills->groupBy('customer_id') as $customerBills) {
            $latestPerCustomer[] = $customerBills->sortByDesc(function ($item) {
                return $item->year * 12 + $item->month;
            })->first()->id;
        }

        foreach ($bills as $bill) {
            $bill->is_latest = in_array($bill->id, $latestPerCustomer);
        }

        return view('livewire.bill-manager', [
            'bills' => $bills
        ]);
    }

    public function updatingFilterMonth()
    {
        $this->resetPage();
    }

    public function updatingFilterYear()
    {
        $this->resetPage();
    }

    public function updatingFilterGroup()
    {
        $this->resetPage();
    }
}