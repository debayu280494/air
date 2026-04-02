<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithoutUrlPagination;
use Livewire\Attributes\On;
use App\Models\Customer;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class CustomerManager extends Component
{
    use WithPagination, WithoutUrlPagination;

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
        'status' => 'required|in:' . Customer::STATUS_AKTIF . ',' . Customer::STATUS_NONAKTIF,
        'service_id' => 'nullable|exists:services,id',
    ];

    public function mount()
    {
        $this->services = Service::pluck('name', 'id');
    }

    private function getCustomers()
    {
        return Customer::query()
            ->with('service:id,name')
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterGroup, fn($q) =>
                $q->where('group_name', 'like', "%{$this->filterGroup}%")
            )
            ->when($this->filterStatus, fn($q) =>
                $q->where('status', $this->filterStatus)
            )
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        if (!in_array($this->sortField, $this->allowedSorts)) {
            $this->sortField = 'created_at';
        }

        $customers = $this->getCustomers()->paginate(10);

        return view('livewire.customer-manager', compact('customers'));
    }

    public function sortBy($field)
    {
        if (!in_array($field, $this->allowedSorts)) return;

        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->gotoPage(1);
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

        DB::transaction(function () {

            Customer::updateOrCreate(
                ['id' => $this->editId],
                [
                    'name' => $this->name,
                    'address' => $this->address,
                    'phone' => $this->phone,
                    'group_name' => $this->group_name,
                    'status' => $this->status,
                    'service_id' => $this->service_id,
                ]
            );

        });

        $this->toast('success', $this->editId ? 'Data berhasil diupdate' : 'Data berhasil ditambahkan');

        $this->closeModal();
        $this->gotoPage(1);
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
        Customer::findOrFail($id)->delete();

        $this->gotoPage(1);

        $this->toast('success', 'Customer berhasil dihapus');
    }

    private function toast($type, $message)
    {
        $this->dispatch('toast', [
            'type' => $type,
            'message' => $message
        ]);
    }

    public function updating($name)
    {
        if (in_array($name, ['search', 'filterGroup', 'filterStatus'])) {
            $this->gotoPage(1);
        }
    }
}