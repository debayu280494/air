<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Service;

class ServiceManager extends Component
{
    public $services = [];

    public $name, $price_per_meter, $maintenance_fee;

    public $editId = null;
    public $isOpen = false;

    public $deleteId = null;

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->services = Service::latest()->get();
    }

    public function render()
    {
        return view('livewire.service-manager');
    }

    // =====================
    // MODAL
    // =====================
    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->reset(['name','price_per_meter','maintenance_fee','editId']);
    }

    // =====================
    // SAVE
    // =====================
    public function save()
    {
        $this->validate([
            'name' => 'required',
            'price_per_meter' => 'required|numeric',
            'maintenance_fee' => 'nullable|numeric',
        ]);

        if ($this->editId) {

            Service::find($this->editId)?->update([
                'name' => $this->name,
                'price_per_meter' => $this->price_per_meter,
                'maintenance_fee' => $this->maintenance_fee ?? 0,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Data berhasil diupdate'
            ]);

        } else {

            Service::create([
                'name' => $this->name,
                'price_per_meter' => $this->price_per_meter,
                'maintenance_fee' => $this->maintenance_fee ?? 0,
            ]);

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Data berhasil ditambahkan'
            ]);
        }

        $this->loadData();
        $this->closeModal();
    }

    // =====================
    // EDIT
    // =====================
    public function edit($id)
    {
        $data = Service::find($id);

        if (!$data) return;

        $this->editId = $id;
        $this->name = $data->name;
        $this->price_per_meter = $data->price_per_meter;
        $this->maintenance_fee = $data->maintenance_fee;

        $this->isOpen = true;
    }

    // =====================
    // DELETE FLOW
    // =====================
    public function confirmDelete($id)
    {
        $this->deleteId = $id;

        $this->dispatch('show-delete-confirm');
    }

    #[On('delete-confirmed')]
    public function deleteConfirmed()
    {
        $data = Service::find($this->deleteId);

        if ($data) {
            $data->delete();

            $this->dispatch('toast', [
                'type' => 'success',
                'message' => 'Data berhasil dihapus'
            ]);
        }

        $this->deleteId = null;
        $this->loadData();
    }
}