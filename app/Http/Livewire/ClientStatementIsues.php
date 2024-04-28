<?php

namespace App\Http\Livewire;

use App\Models\Client;
use App\Models\IssueClientStatement;
use App\Models\Order;
use Livewire\Component;

class ClientStatementIsues extends Component
{



    public $client_id;
    public $from_date;
    public $to_date, $client, $client_name;
    public $total_amount_in = 0;
    public $total_amount_out = 0;
    public $client_current_account_balance = 0;
    public $status;

    public function hydrate()
    {
        $this->emit("select2");
        $this->total_amount_in = 0;
        $this->total_amount_out = 0;
    }

    public function getData()
    {
        $this->client_name = null;
        return $data = IssueClientStatement::latest()->paginate(1000);
    }
    // public function updatedStatus($val){
    //     // dd($val);
    // }
    public function getDataWithName()
    {
        $fromDate = $this->from_date;
        $ToDate = $this->to_date;
        $Status = $this->status;
        $this->client_name = Client::find($this->client_id)->fullname  ?? ' ';
        $data = IssueClientStatement::when($this->client_id, function ($query, $client_id) {
            $query->where('client_id', $client_id);
        })
            ->when($fromDate, function ($query, $from_date) {
                return $query->where('issue_date', '>', $from_date);
            })->when($ToDate, function ($query, $from_date) {
                return $query->where('issue_date', '<', $from_date);
            });
        if ($this->status != 'all') {
            $data->when($this->status, function ($query, $status) {
                return $query->where('status', $status);
            });
        }
        return $data->latest()
            ->paginate(1000);
    }
    public function render()
    {
        $Client = Client::all();
        if ($this->client_id || ($this->from_date || $this->to_date)) $data =  $this->getDataWithName();
        else $data = $this->getData();
        if ($this->status && $this->status != 'all') {
            if (!!$data->count()) $data = $data->toQuery()->where('status', $this->status)->paginate(1000);
        }
        $total_fess = $data->sum('total_fees');
        $total_delevirey = $data->sum('delivery_fees');
        $total_delevirey_inner  = $data->where('service_id', 2)->sum('delivery_fees');
        $total_delevirey_outer = $data->where('service_id', 3)->sum('delivery_fees');
        $total_of_total = ($total_fess - $total_delevirey_outer) - $total_delevirey_inner;
        return view('livewire.client-statement-isues', [
            "data" => $data,
            "clients" => $Client,
            'total_delevirey_inner' => $total_delevirey_inner,
            'total_delevirey_outer' => $total_delevirey_outer,
            'total_fess' => $total_fess,
            'total_of_total' => $total_of_total,
            'total_delevirey' => $total_delevirey,
            'client_name' => $this->client_name,
        ])
            ->layout('layouts.master');
    }
}
