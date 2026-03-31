<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\Service;

class CustomerManager extends Component
{
    public $customers, $services;

    public $customer_code, $name, $address, $phone, $group_name, $status = 'aktif', $service_id;

    public $editId = null;
    public $isOpen = false;

    public function render()
    {
        $this->customers = Customer::with('service')->latest()->get();
        $this->services = Service::all();

        return view('livewire.customer-manager');
    }

    // 🔥 AUTO KODE (N001)
    public function generateCode()
    {
        $last = Customer::latest()->first();

        if (!$last) {
            return 'N001';
        }

        $number = (int) substr($last->customer_code, 1) + 1;
        return 'N' . str_pad($number, 3, '0', STR_PAD_LEFT);
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
        $this->reset([
            'name','address','phone','group_name','service_id','status','editId'
        ]);

        $this->status = 'aktif';
    }

    // 🔥 SAVE / UPDATE
    public function save()
    {
        if ($this->editId) {

            Customer::find($this->editId)->update([
                'name' => $this->name,
                'address' => $this->address,
                'phone' => $this->phone,
                'group_name' => $this->group_name,
                'status' => $this->status,
                'service_id' => $this->service_id,
            ]);

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
        }

        $this->closeModal();
    }

    // 🔥 EDIT
    public function edit($id)
    {
        $data = Customer::find($id);

        $this->editId = $id;
        $this->name = $data->name;
        $this->address = $data->address;
        $this->phone = $data->phone;
        $this->group_name = $data->group_name;
        $this->status = $data->status;
        $this->service_id = $data->service_id;

        $this->isOpen = true;
    }

    // 🔥 DELETE
    public function delete($id)
    {
        Customer::find($id)->delete();
    }
}