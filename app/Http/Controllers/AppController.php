<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Song;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

    public function offline(){
        return view('app.offline');
    }

    public function app(){
        return view('app.home');
    }

    public function worship(){
        /*$data['routeName']=$this->routeName;
        $data['member']=$this->member;*/
        $today = date('Y-m-d');
        $dates = Service::query()->selectRaw('DATE(servicedate) as date')->distinct()->orderBy('date', 'asc')->pluck('date');
        $pastDate = $dates->filter(fn ($d) => $d <= $today)->last();
        $futureDates = $dates->filter(fn ($d) => $d > $today)->take(3);
        $displayDates = collect([$pastDate])->merge($futureDates)->filter();
        $data['services'] = Service::query()->whereIn(DB::raw('DATE(servicedate)'), $displayDates)->orderBy('servicedate')->get()
            ->groupBy(fn ($s) => $s->servicedate);
        return view('app.worship',$data);
    }

    public function service($id){
        $data['service'] = Service::find($id);
        $serviceTime = $data['service']->servicetime;

        $lastUsedSubquery = DB::table('setitems')
            ->selectRaw('setitems.content_id, MAX(services.servicedate) as last_used_date')
            ->join('services', 'services.id', '=', 'setitems.service_id')
            ->where('setitems.content_type', 'song')
            ->where('services.servicetime', $serviceTime)
            ->groupBy('setitems.content_id');

        $songsQuery = Song::query()
            ->leftJoinSub($lastUsedSubquery, 'last_used', function ($join) {
                $join->on('songs.id', '=', 'last_used.content_id');
            })
            ->select('songs.*', 'last_used.last_used_date');
        if ($search = request('search')) {
            $songsQuery->where(function ($q) use ($search) {
                $q->where('songs.title', 'like', "%{$search}%");

                if (strlen($search) >= 3) {
                    $q->orWhereFullText(['songs.title', 'songs.lyrics'], $search);
                }
            });
        }
        $types = request('types', ['hymn', 'contemporary']);
        $songsQuery->whereIn('songs.musictype', $types);
        if ($tempo = request('tempo')) {
            $songsQuery->where('songs.tempo', $tempo);
        }

        if ($tag = request('tag')) {
            $songsQuery->where('songs.tags', 'like', "%{$tag}%");
        }
        $songsQuery->orderBy('songs.title');
        $data['songs'] = $songsQuery->limit(50)->get();
        $data['candidates'] = [];
        $data['orderItems'] = [];
        return view('app.service',$data);
    }


}
