<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use App\Models\Customer;
use App\Models\Service;

class CustomerManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // ================= FILTER =================
    public $search = '';
    public $filterGroup = '';
    public $filterStatus = '';

    // ================= SORT =================
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $allowedSorts = ['name', 'created_at', 'group_name', 'status'];

    // ================= DATA =================
    public $services = [];

    // ================= MODAL =================
    public $isOpen = false;
    public $editId = null;

    // ================= FORM =================
    public $name;
    public $address;
    public $phone;
    public $group_name;
    public $status = 'aktif';
    public $service_id;

    protected $rules = [
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'group_name' => 'nullable|string|max:100',
        'status' => 'required|in:aktif,nonaktif',
        'service_id' => 'nullable|exists:services,id',
    ];

    public function mount()
    {
        $this->services = Service::select('id','name')->get();
    }

    public function render()
    {
        if (!in_array($this->sortField, $this->allowedSorts)) {
            $this->sortField = 'created_at';
        }

        $customers = Customer::select('id','name','address','phone','group_name','status','service_id','created_at')
            ->with('service:id,name')
            ->when($this->search, fn($q) =>
                $q->where(function($q){
                    $q->where('name','like','%'.$this->search.'%')
                      ->orWhere('phone','like','%'.$this->search.'%');
                })
            )
            ->when($this->filterGroup, fn($q) =>
                $q->whereRaw('LOWER(TRIM(group_name)) = ?', [strtolower(trim($this->filterGroup))])
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.customer-manager', compact('customers'));
    }

    public function updatedSearch() { $this->resetPage(); }
    public function updatedFilterGroup() { $this->resetPage(); }
    public function updatedFilterStatus() { $this->resetPage(); }

    public function sortBy($field)
    {
        if (!in_array($field, $this->allowedSorts)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->resetErrorBag();
        $this->isOpen = false;
    }

    public function resetForm()
    {
        $this->reset(['name','address','phone','group_name','service_id','editId']);
        $this->status = 'aktif';
    }

    public function save()
    {
        $this->validate();

        $this->group_name = $this->group_name
            ? ucwords(strtolower(trim($this->group_name)))
            : null;

        if ($this->editId) {

            Customer::findOrFail($this->editId)->update([
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'group_name' => $this->group_name,
                'status' => $this->status,
                'service_id' => $this->service_id,
            ]);

            $this->toast('success', 'Data berhasil diupdate');

        } else {

            Customer::create([
                'customer_code' => $this->generateCode(),
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'group_name' => $this->group_name,
                'status' => $this->status,
                'service_id' => $this->service_id,
            ]);

            $this->toast('success', 'Data berhasil ditambahkan');
        }

        $this->closeModal();
        $this->resetPage();
    }

    public function edit($id)
    {
        $data = Customer::findOrFail($id);

        $this->editId = $id;

        $this->name = $data->name;
        $this->address = $data->address;
        $this->phone = $data->phone;
        $this->group_name = $data->group_name;
        $this->status = $data->status;
        $this->service_id = $data->service_id;

        $this->isOpen = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('show-delete-confirm', id: $id);
    }

    #[On('delete-confirmed')]
    public function deleteConfirmed($id)
    {
        $customer = Customer::find($id);

        if (!$customer) {
            $this->toast('error', 'Data tidak ditemukan');
            return;
        }

        $customer->delete();

        $this->resetPage();
        $this->toast('success', 'Data berhasil dihapus');
    }

    public function generateCode()
    {
        $lastCode = Customer::max('customer_code');

        if (!$lastCode) {
            return 'C001';
        }

        $number = (int) str_replace('C', '', $lastCode);
        $number++;

        return 'C' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function toast($type, $message)
    {
        $this->dispatch('toast', [
            'type' => $type,
            'message' => $message
        ]);
    }
}