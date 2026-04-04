<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as FormRequest;

class WebController extends Controller
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

    public function home(FormRequest $request)
    {
        /*$today=date('Y-m-d');
        if (null!==$request->input('message')){
            $data['message'] = $request->input('message');
            $data['user'] = $request->input('user');
            Mail::to(setting('email.church_email'))->queue(new MessageMail($data));
            $data['notification']="Thank you! We will reply to you by email";
        }
        $data['blogs']=Post::with('person')->where('published',1)->orderBy('published_at','DESC')->take(3)->get();
        $data['upcoming']=Service::withWhereHas('setitems', function($q) { $q->where('setitemable_type','song'); })->where('servicedate','>=',$today)->whereNotNull('video')->orderBy('servicedate','ASC')->first();
        $data['sermon']=Service::with('person','series')->whereNotNull('audio')->orderBy('servicedate','DESC')->first();
        $data['pageName'] = "Home";*/
        $data=[];
        return view('church::web.home',$data);
    }


}
