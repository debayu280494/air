<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Usage;
use App\Models\Customer;
use App\Models\Bill;

class UsageManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterMonth' => ['except' => ''],
        'filterYear' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'filterGroup' => ['except' => ''],
    ];

    public $customers = [];
    public $groups = [];
    public $years = [];

    // FILTER
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

    public $meter_start;
    public $meter_end;
    public $usagePreview = 0;

    public $meterReadonly = false;
    public $duplicateWarning = false;

    public $keterangan;
    public $isOpen = false;
    public $isEdit = false;

    protected $listeners = ['delete-confirmed' => 'deleteConfirmed'];

    public function getLastUsageIdsProperty()
    {
        return Usage::getLastIds();
    }
    
    public function mount()
    {
        $this->customers = Customer::where('status', 'aktif')
        ->orderBy('name', 'asc')
        ->get();

        $this->groups = Customer::select('group_name')
            ->whereNotNull('group_name')
            ->distinct()
            ->pluck('group_name');

        $currentYear = date('Y');

        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
            $this->years[] = $i;
        }
    }

    // ================= FILTER RESET =================
    public function updating($name)
    {
        if (in_array($name, ['search','filterMonth','filterYear','filterStatus','filterGroup'])) {
            $this->resetPage();
        }
    }

    // ================= DATA =================
    public function getUsagesProperty()
    {
        return $this->buildUsageQuery()
            ->orderByDesc('id')
            ->paginate(10);
    }
    private function buildUsageQuery()
    {
        return Usage::query()
            ->select('id','customer_id','month','year','meter_start','meter_end','usage','total_bill')
            ->with([
                'customer:id,name,group_name',
                'bill:id,usage_id,status'
            ])
            ->when($this->search, function ($q) {
                $q->whereHas('customer', function ($c) {
                    $c->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterGroup, fn($q) =>
                $q->whereHas('customer', fn($c) =>
                    $c->where('group_name', $this->filterGroup)
                )
            )
            ->when($this->filterMonth, fn($q) =>
                $q->where('month', $this->filterMonth)
            )
            ->when($this->filterYear, fn($q) =>
                $q->where('year', $this->filterYear)
            )
            ->when($this->filterStatus, fn($q) =>
                $q->whereHas('bill', fn($b) =>
                    $b->where('status', $this->filterStatus)
                )
            );
    }

    public function updated($name)
    {
        if (in_array($name, ['customer_id','month','year'])) {
            $this->updateMeterStart();
            $this->checkDuplicate();
        }

        if ($name === 'meter_end') {
            $this->calculate();
        }
    }


    // ================= LOGIC =================
    private function updateMeterStart()
    {
        if (!$this->customer_id || !$this->month || !$this->year) {
            $this->meter_start = null;
            $this->meterReadonly = false;
            return;
        }

        $last = Usage::lastByCustomer($this->customer_id);

        $this->meter_start = $last ? (int) $last->meter_end : 0;

        $this->meterReadonly = $this->isEdit || $last;
    }

    private function calculate()
    {
        $this->usagePreview = max(
            0,
            (int) $this->meter_end - (int) $this->meter_start
        );
    }


    // ================= SAVE =================
    public function save()
    {
        $this->validateData();

        if ($this->isDuplicateData()) return;

        if (!$this->isEdit && !$this->isNextMonthValid()) return;

        $this->storeUsage();

        $this->closeModal();
        $this->resetPage();
        $this->resetFilter();
    }

    private function validateData()
    {
        $this->validate([
            'customer_id' => 'required|exists:customers,id',
            'month' => 'required|numeric|min:1|max:12',
            'year' => 'required|numeric',
            'meter_end' => [
                'required',
                'numeric',
                function ($attr, $value, $fail) {
                    if ($this->meter_start !== null && $value < $this->meter_start) {
                        $fail('Meter akhir tidak boleh lebih kecil dari meter awal.');
                    }
                }
            ]
        ]);
    }

    private function isDuplicateData()
    {
        if (Usage::isDuplicate(
            $this->customer_id,
            $this->month,
            $this->year,
            $this->usage_id
        )) {
            $this->addError('month', 'Data bulan ini sudah ada');
            return true;
        }

        return false;
    }

    private function storeUsage()
    {
        $usageValue = $this->meter_end - $this->meter_start;
        $total = $usageValue * 5000;

        $usage = Usage::updateOrCreate(
            ['id' => $this->usage_id],
            [
                'customer_id' => $this->customer_id,
                'month' => $this->month,
                'year' => $this->year,
                'meter_start' => $this->meter_start,
                'meter_end' => $this->meter_end,
                'usage' => $usageValue,
                'total_bill' => $total,
                'keterangan' => $this->keterangan,
            ]
        );

        Bill::updateOrCreate(
            ['usage_id' => $usage->id],
            [
                'customer_id' => $this->customer_id,
                'month' => $this->month,
                'year' => $this->year,
                'total_bill' => $total,
                'status' => 'belum',
            ]
        );
    }

    private function isNextMonthValid()
    {
        if (!Usage::isNextMonthValid(
            $this->customer_id,
            $this->year,
            $this->month
        )) {
            $this->addError('month', 'Harus bulan berikutnya dari data terakhir');
            return false;
        }

        return true;
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $data = Usage::findOrFail($id);

        if ($data->bill && $data->bill->status === 'lunas') {
            $this->addError('edit', 'Data sudah lunas!');
            return;
        }

        $last = Usage::lastByCustomer($data->customer_id);

        if (!$last || $last->id !== $data->id) {
            $this->addError('edit', 'Hanya data terakhir!');
            return;
        }

        $this->fill([
            'usage_id' => $data->id,
            'customer_id' => $data->customer_id,
            'month' => $data->month,
            'year' => $data->year,
            'meter_start' => $data->meter_start,
            'meter_end' => $data->meter_end,
            'keterangan' => $data->keterangan,
        ]);

        $this->isEdit = true;
        $this->meterReadonly = true;
        $this->isOpen = true;

        $this->calculate();

        $this->resetPage();
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

        if ($target->bill && $target->bill->status === 'lunas') {
            $this->addError('delete', 'Tidak bisa hapus, sudah lunas!');
            return;
        }

        if ($target->bill) {
            $target->bill->delete();
        }

        $target->delete();
    }

    // ================= MODAL =================
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
        ]);

        $this->isEdit = false;
        $this->meterReadonly = false;
    }

    private function checkDuplicate()
    {
        if (!$this->customer_id || !$this->month || !$this->year) {
            $this->duplicateWarning = false;
            return;
        }

        $this->duplicateWarning = Usage::isDuplicate(
            $this->customer_id,
            $this->month,
            $this->year,
            $this->usage_id
        );
    }

    public function getMonthsProperty()
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
    }

    

    public function resetFilter()
    {
        $this->reset([
            'search',
            'filterMonth',
            'filterYear',
            'filterStatus',
            'filterGroup',
        ]);

        $this->resetPage();
    }
   

    public function render()
    {
        return view('livewire.usage-manager', [
            'usages' => $this->usages
        ]);
    }
}