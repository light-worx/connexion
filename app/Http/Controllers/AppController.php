<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

class AppController extends Controller
{

    public $member, $routeName;

    public function __construct(){
        /*$this->member=Config::get('member');
        $routename = Request::route()->getName();
        if (str_contains($routename,'app.')){
            $this->routeName="app";
        } else {
            $this->routeName="web";
        }*/
    }

    public function app(){
        /*$data['routeName']=$this->routeName;
        $data['member']=$this->member;*/
        $data=[];
        return view('app.home',$data);
    }

    public function worship(){
        $data=[];
        return view('app.worship',$data);
    }


}
