<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Usage;
use App\Models\Customer;

class UsageManager extends Component
{
    public $customers = [];
    public $usages = [];

    public $usage_id;
    public $customer_id;
    public $month;
    public $year;
    public $years = [];

    public $meter_start = 0;
    public $meter_end;
    public $usagePreview = 0;

    public $keterangan;
    public $isOpen = false;

    public $duplicateWarning = false;

    public function mount()
    {
        $this->customers = Customer::where('status', 'aktif')->get();
         // generate tahun (5 tahun ke depan & 5 tahun ke belakang)
        $currentYear = date('Y');

        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
            $this->years[] = $i;
        }
        $this->loadData();
    }

    public function loadData()
    {
        $this->usages = Usage::with('customer')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
    }

    // =====================
    // REALTIME UPDATE
    // =====================
    public function updated($name, $value)
    {
        $this->{$name} = $value;

        if (in_array($name, ['customer_id', 'month', 'year'])) {
            $this->setMeterStart();
            $this->checkDuplicate();
        }

        if (in_array($name, ['meter_start', 'meter_end'])) {
            $this->calculate();
        }
    }

    // =====================
    // CEK DUPLIKAT
    // =====================
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

    // =====================
    // METER START (PDAM STYLE)
    // =====================
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
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        $this->meter_start = $last ? (int) $last->meter_end : 0;

        $this->calculate();
    }

    // =====================
    // HITUNG PEMAKAIAN
    // =====================
    private function calculate()
    {
        if ($this->meter_end === null) {
            $this->usagePreview = 0;
            return;
        }

        $this->usagePreview = max(
            0,
            (int) $this->meter_end - (int) $this->meter_start
        );
    }

    // =====================
    // MODAL
    // =====================
    public function openModal()
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

        $this->isOpen = true;
    }

    // =====================
    // SAVE
    // =====================
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

        // 🔥 VALIDASI SEKUENSIAL (INI YANG BARU)
        if (!$this->validateSequence()) {
            $this->addError('month', 'Tidak boleh input bulan/tahun mundur dari data terakhir');
            return;
        }

        // VALIDASI METER
        if ((int)$this->meter_end < (int)$this->meter_start) {
            $this->addError('meter_end', 'Meter akhir tidak boleh lebih kecil');
            return;
        }

        $usage = (int)$this->meter_end - (int)$this->meter_start;
        $total = $usage * 5000;

        Usage::updateOrCreate(
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

        $this->isOpen = false;
        $this->loadData();
    }

    private function validateSequence()
    {
        if (!$this->customer_id || !$this->month || !$this->year) {
            return true;
        }

        $last = Usage::where('customer_id', $this->customer_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if (!$last) {
            return true; // data pertama
        }

        // ubah ke angka untuk dibandingkan
        $lastValue = ($last->year * 12) + $last->month;
        $currentValue = ($this->year * 12) + $this->month;

        if ($currentValue < $lastValue) {
            return false;
        }

        return true;
    }

    public function delete($id)
    {
        $target = Usage::find($id);

        if (!$target) return;

        // cek urutan (harus terakhir)
        $last = Usage::where('customer_id', $target->customer_id)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->first();

        if ($last && $last->id !== $target->id) {
            $this->addError('delete', 'Hapus harus urut dari data terakhir!');
            return;
        }

        $target->delete();

        // 🔥 WAJIB: paksa reload + reset error
        $this->resetErrorBag();
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.usage-manager');
    }
}