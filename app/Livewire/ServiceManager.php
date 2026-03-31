<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Service;

class ServiceManager extends Component
{
    public $services;

    public $name, $price_per_meter, $maintenance_fee;

    public $editId = null;
    public $isOpen = false;

    public function render()
    {
        $this->services = Service::latest()->get();

        return view('livewire.service-manager');
    }

    // 🔥 OPEN MODAL
    public function openModal()
    {
        $this->resetForm();
        $this->isOpen = true;
    }

    // 🔥 CLOSE MODAL
    public function closeModal()
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    // 🔥 RESET FORM
    public function resetForm()
    {
        $this->reset(['name','price_per_meter','maintenance_fee','editId']);
    }

    // 🔥 SAVE / UPDATE
    public function save()
    {
        if ($this->editId) {

            Service::find($this->editId)->update([
                'name' => $this->name,
                'price_per_meter' => $this->price_per_meter,
                'maintenance_fee' => $this->maintenance_fee ?? 0,
            ]);

        } else {

            Service::create([
                'name' => $this->name,
                'price_per_meter' => $this->price_per_meter,
                'maintenance_fee' => $this->maintenance_fee ?? 0,
            ]);
        }

        $this->closeModal();
    }

    // 🔥 EDIT
    public function edit($id)
    {
        $data = Service::find($id);

        $this->editId = $id;
        $this->name = $data->name;
        $this->price_per_meter = $data->price_per_meter;
        $this->maintenance_fee = $data->maintenance_fee;

        $this->isOpen = true;
    }

    // 🔥 DELETE
    public function delete($id)
    {
        Service::find($id)->delete();
    }
}