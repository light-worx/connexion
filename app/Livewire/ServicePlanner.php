<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Song;
use App\Models\Prayer;
use App\Models\Setitem;

class ServicePlanner extends Component
{
    public $service;
    public $plan;

    public $search = '';

    public $songs = [];
    public $liturgy = [];

    public $plannedSongs = [];
    public $plannedLiturgy = [];
    public $orderItems = [];

    public function mount($service)
    {
        $this->service = $service;
        $this->plan = $service->plan;

        $this->loadAll();
    }

    public function updatedSearch()
    {
        $this->loadSongs();
        $this->loadLiturgy();
    }

    public function loadAll()
    {
        $this->loadSongs();
        $this->loadLiturgy();
        $this->loadPlanned();
        $this->loadOrder();
    }

    // ----------------------
    // SONG SEARCH
    // ----------------------
    public function loadSongs()
    {
        $serviceTime = $this->service->servicetime;

        $lastUsed = DB::table('setitems')
            ->selectRaw('content_id, MAX(services.servicedate) as last_used_date')
            ->join('services', 'services.id', '=', 'setitems.service_id')
            ->where('content_type', 'song')
            ->where('services.servicetime', $serviceTime)
            ->groupBy('content_id');

        $query = Song::query()
            ->leftJoinSub($lastUsed, 'lu', fn($j) => $j->on('songs.id', '=', 'lu.content_id'))
            ->select('songs.*', 'lu.last_used_date');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('songs.title', 'like', "%{$this->search}%");

                if (strlen($this->search) >= 3) {
                    $q->orWhereFullText(['songs.title', 'songs.lyrics'], $this->search);
                }
            });
        }

        $this->songs = $query->limit(30)->get();
    }

    // ----------------------
    // LITURGY SEARCH
    // ----------------------
    public function loadLiturgy()
    {
        $query = Prayer::query();

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%")
                  ->orWhere('content', 'like', "%{$this->search}%");
        }

        $this->liturgy = $query->limit(30)->get();
    }

    // ----------------------
    // PLANNED ITEMS
    // ----------------------
    public function loadPlanned()
    {
        if (isset($his->plan->id)){
            $items = Setitem::where('service_plan_id', $this->plan->id)->get();
            $this->plannedSongs = $items->where('content_type', 'song');
            $this->plannedLiturgy = $items->where('content_type', 'prayer');
        } else {
            $items=[];
            $this->plannedSongs = [];
            $this->plannedLiturgy = [];
        }
    }

    // ----------------------
    // ORDER
    // ----------------------
    public function loadOrder()
    {
        $this->orderItems = Setitem::where('service_id', $this->service->id)
            ->orderBy('sort_order')
            ->get();
    }

    // ----------------------
    // ACTIONS
    // ----------------------
    public function addSong($id)
    {
        $song = Song::find($id);

        Setitem::create([
            'service_plan_id' => $this->plan->id,
            'content_type' => 'song',
            'content_id' => $song->id,
            'title' => $song->title,
        ]);

        $this->loadPlanned();
    }

    public function addLiturgy($id)
    {
        $item = Prayer::find($id);

        Setitem::create([
            'service_plan_id' => $this->plan->id,
            'content_type' => 'prayer',
            'content_id' => $item->id,
            'title' => $item->title,
        ]);

        $this->loadPlanned();
    }

    public function toOrder($id)
    {
        $item = Setitem::find($id);

        $next = Setitem::where('service_id', $this->service->id)->max('sort_order') + 1;

        Setitem::create([
            'service_id' => $this->service->id,
            'content_type' => $item->content_type,
            'content_id' => $item->content_id,
            'title' => $item->title,
            'sort_order' => $next,
        ]);

        $this->loadOrder();
    }

    public function removePlanned($id)
    {
        Setitem::find($id)?->delete();
        $this->loadPlanned();
    }

    public function removeOrder($id)
    {
        Setitem::find($id)?->delete();
        $this->loadOrder();
    }

    public function render()
    {
        return view('livewire.service-planner');
    }
}