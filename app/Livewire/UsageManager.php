<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Usage;
use App\Models\Customer;
use App\Models\Bill;
use Illuminate\Pagination\LengthAwarePaginator;


class UsageManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $customers = [];
    public $groups = [];
    
    public $years = [];

    // FILTER + SEARCH
    public $search = '';
    public $filterMonth = '';
    public $filterYear = '';
    public $filterStatus = '';
    public $filterGroup = '';

    // FORM
    public $usage_id;
    public $customer_id;
    public $month;
    public $year;

    public $meter_start = 0;
    public $meter_end;
    public $usagePreview = 0;

    public $keterangan;
    public $isOpen = false;

    public $duplicateWarning = false;

    protected $listeners = ['delete-confirmed' => 'deleteConfirmed'];

    public function mount()
    {
        $this->customers = Customer::where('status', 'aktif')->get();

        // 🔥 ambil group unik
        $this->groups = Customer::select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        $currentYear = date('Y');
        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
            $this->years[] = $i;
        }
    }

    public function updating($name)
    {
        $this->resetPage();
    }

    private function isFiltering()
    {
        return $this->search 
            || $this->filterGroup 
            || $this->filterMonth 
            || $this->filterYear 
            || $this->filterStatus;
    }
    // ================= LOAD DATA =================
    public function getUsagesProperty()
    {
        $query = Usage::with(['customer', 'bill'])

            ->when($this->search, function ($q) {
                $q->whereHas('customer', fn($c) =>
                    $c->where('name', 'like', "%{$this->search}%")
                );
            })
            ->when($this->filterGroup, function ($q) {
                $q->whereHas('customer', fn($c) =>
                    $c->where('group_name', $this->filterGroup)
                );
            })
            ->when($this->filterMonth, fn($q) =>
                $q->where('month', $this->filterMonth)
            )
            ->when($this->filterYear, fn($q) =>
                $q->where('year', $this->filterYear)
            )
            ->when($this->filterStatus, function ($q) {
                $q->whereHas('bill', fn($b) =>
                    $b->where('status', $this->filterStatus)
                );
            });

        // 🔥 SORTING UTAMA
        if ($this->isFiltering()) {
            // kalau ada filter → urut nama
            $query->join('customers', 'usages.customer_id', '=', 'customers.id')
                ->orderBy('customers.name', 'asc')
                ->select('usages.*');
        } else {
            // kalau tidak ada filter → urut data terbaru
            $query->orderByDesc('id'); // 🔥 PALING AMAN
        }

        $data = $query->get();

        $latestIds = [];

        foreach ($data->groupBy('customer_id') as $items) {
            $latestIds[] = $items->first()->id;
        }

        $data = $data->map(function ($item) use ($latestIds) {
            $item->is_last = in_array($item->id, $latestIds);
            return $item;
        });

        return $this->paginateCollection($data, 10);
    }

    // ================= FORM CHANGE =================
    public function updated($name, $value)
    {
        $this->{$name} = $value;

        if (in_array($name, ['customer_id', 'month', 'year'])) {
            $this->setMeterStart();
            $this->checkDuplicate();
        }

        if ($name === 'meter_end') {
            $this->calculate();
        }
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $data = Usage::find($id);
        if (!$data) return;

        $bill = Bill::where('usage_id', $data->id)->first();

        // ❗ CEGAH EDIT jika sudah lunas
        if ($bill && $bill->status === 'lunas') {
            $this->addError('edit', 'Data sudah lunas, tidak bisa diedit!');
            return;
        }

        $last = Usage::where('customer_id', $data->customer_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if ($last->id !== $data->id) {
            $this->addError('edit', 'Hanya data terakhir yang bisa diedit!');
            return;
        }

        $this->resetErrorBag();

        $this->usage_id = $data->id;
        $this->customer_id = $data->customer_id;
        $this->month = $data->month;
        $this->year = $data->year;
        $this->meter_start = $data->meter_start;
        $this->meter_end = $data->meter_end;
        $this->keterangan = $data->keterangan;

        $this->calculate();
        $this->isOpen = true;
    }

    // ================= DUPLIKAT =================
    private function checkDuplicate()
    {
        if (!$this->customer_id || !$this->month || !$this->year) {
            $this->duplicateWarning = false;
            return;
        }

        $this->duplicateWarning = Usage::where('customer_id', $this->customer_id)
            ->where('month', $this->month)
            ->where('year', $this->year)
            ->when($this->usage_id, fn($q) => $q->where('id', '!=', $this->usage_id))
            ->exists();
    }

    // ================= METER START =================
    private function setMeterStart()
    {
        if (!$this->customer_id || !$this->month || !$this->year) {
            $this->meter_start = 0;
            return;
        }

        $last = Usage::where('customer_id', $this->customer_id)
            ->where(function ($q) {
                $q->where('year', '<', $this->year)
                  ->orWhere(function ($q2) {
                      $q2->where('year', $this->year)
                         ->where('month', '<', $this->month);
                  });
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        $this->meter_start = $last ? (int)$last->meter_end : 0;
        $this->calculate();
    }

    private function calculate()
    {
        $this->usagePreview = $this->meter_end
            ? max(0, (int)$this->meter_end - (int)$this->meter_start)
            : 0;
    }

    // ================= SAVE =================
    public function save()
    {
        $this->validate([
            'customer_id' => 'required',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric',
            'meter_end' => 'required|numeric',
        ]);

        if ($this->duplicateWarning) {
            $this->addError('month', 'Data bulan ini sudah ada');
            return;
        }

        if (!$this->validateSequence()) {
            $this->addError('month', 'Harus bulan berikutnya dari data terakhir');
            return;
        }

        if ((int)$this->meter_end < (int)$this->meter_start) {
            $this->addError('meter_end', 'Meter akhir tidak boleh lebih kecil');
            return;
        }

        $usage = $this->meter_end - $this->meter_start;
        $total = $usage * 5000;

        $usage = Usage::updateOrCreate(
            ['id' => $this->usage_id],
            [
                'customer_id' => $this->customer_id,
                'month' => $this->month,
                'year' => $this->year,
                'meter_start' => $this->meter_start,
                'meter_end' => $this->meter_end,
                'usage' => $usage,
                'total_bill' => $total,
                'keterangan' => $this->keterangan,
            ]
        );

        // 🔥 SIMPAN / UPDATE BILL
        Bill::updateOrCreate(
            ['usage_id' => $usage->id], // ✅ pakai $usage
            [
                'customer_id' => $this->customer_id,
                'month' => $this->month,
                'year' => $this->year,
                'total_bill' => $total,
                'status' => 'belum',
                'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad(rand(1,9999), 4, '0', STR_PAD_LEFT),
            ]
        );

        $this->closeModal();
    }

    private function validateSequence()
    {
        $last = Usage::where('customer_id', $this->customer_id)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->first();

        if (!$last) return true;

        $lastMonthIndex = ($last->year * 12) + $last->month;
        $currentMonthIndex = ($this->year * 12) + $this->month;

        // 🔥 HARUS SELISIH = 1 BULAN
        return $currentMonthIndex === ($lastMonthIndex + 1);
    }

    // ================= DELETE =================
    public function delete($id)
    {
        $this->dispatch('show-delete-confirm', id: $id);
    }

    public function deleteConfirmed($id)
    {
        $target = Usage::find($id);
        if (!$target) return;

        $bill = Bill::where('usage_id', $target->id)->first();

        // ❗ CEGAH kalau sudah lunas
        if ($bill && $bill->status === 'lunas') {
            $this->addError('delete', 'Tidak bisa hapus, sudah lunas!');
            return;
        }

        // ❗ OPTIONAL: hapus bill dulu kalau belum lunas
        if ($bill) {
            $bill->delete();
        }

        $target->delete();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->isOpen = false;
        $this->resetErrorBag();
    }

    private function resetForm()
    {
        $this->reset([
            'usage_id',
            'customer_id',
            'month',
            'year',
            'meter_start',
            'meter_end',
            'usagePreview',
            'keterangan',
            'duplicateWarning'
        ]);
    }

    private function paginateCollection($items, $perPage)
    {
        $page = request()->get('page', 1);

        $offset = ($page - 1) * $perPage;

        return new LengthAwarePaginator(
            $items->slice($offset, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function render()
    {
        return view('livewire.usage-manager', [
            'usages' => $this->usages
        ]);
    }
}