<div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button class="nav-link active"
                    data-bs-toggle="tab"
                    data-bs-target="#songsTab">
                Songs
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#liturgyTab">
                Liturgy
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link"
                    data-bs-toggle="tab"
                    data-bs-target="#orderTab">
                Order
            </button>
        </li>
    </ul>

    <div class="tab-content">

        <!-- =========================
             SONGS TAB
        ========================== -->
        <div class="tab-pane fade show active" id="songsTab">

            <!-- PLANNED SONGS -->
            <div class="card mb-3">
                <div class="card-header py-2">
                    Planned Songs
                </div>

                <div class="card-body p-2">
                    @forelse($plannedSongs as $item)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">

                            <div>
                                <strong>{{ $item->title }}</strong>
                            </div>

                            <div class="d-flex gap-1">
                                <button wire:click="toOrder({{ $item->id }})"
                                        class="btn btn-sm btn-outline-success">
                                    →
                                </button>

                                <button wire:click="removePlanned({{ $item->id }})"
                                        class="btn btn-sm btn-outline-danger">
                                    ✕
                                </button>
                            </div>

                        </div>
                    @empty
                        <div class="text-muted small">No planned songs yet</div>
                    @endforelse
                </div>
            </div>

            <!-- SEARCH -->
            <input type="text"
                   class="form-control mb-2"
                   placeholder="Search songs..."
                   wire:model="search">

            <!-- RESULTS -->
            <div class="list-group">
                @forelse($songs as $song)
                    <div class="list-group-item d-flex justify-content-between align-items-start">

                        <div>
                            <div class="fw-bold">{{ $song->title }}</div>

                            <small class="text-muted">
                                {{ ucfirst($song->music_type ?? '') }}
                                @if($song->tempo)
                                    • {{ $song->tempo }}
                                @endif
                            </small><br>

                            <small class="text-muted">
                                @if($song->last_used_date)
                                    Last used:
                                    {{ \Carbon\Carbon::parse($song->last_used_date)->diffForHumans() }}
                                @else
                                    Never used
                                @endif
                            </small>
                        </div>

                        <button wire:click="addSong({{ $song->id }})"
                                class="btn btn-sm btn-outline-primary">
                            + Plan
                        </button>

                    </div>
                @empty
                    <div class="text-muted p-2">No songs found</div>
                @endforelse
            </div>

        </div>

        <!-- =========================
             LITURGY TAB
        ========================== -->
        <div class="tab-pane fade" id="liturgyTab">

            <!-- PLANNED LITURGY -->
            <div class="card mb-3">
                <div class="card-header py-2">
                    Planned Liturgy
                </div>

                <div class="card-body p-2">
                    @forelse($plannedLiturgy as $item)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">

                            <div>
                                <strong>{{ $item->title }}</strong>
                            </div>

                            <div class="d-flex gap-1">
                                <button wire:click="toOrder({{ $item->id }})"
                                        class="btn btn-sm btn-outline-success">
                                    →
                                </button>

                                <button wire:click="removePlanned({{ $item->id }})"
                                        class="btn btn-sm btn-outline-danger">
                                    ✕
                                </button>
                            </div>

                        </div>
                    @empty
                        <div class="text-muted small">No planned liturgy yet</div>
                    @endforelse
                </div>
            </div>

            <!-- SEARCH -->
            <input type="text"
                   class="form-control mb-2"
                   placeholder="Search liturgy..."
                   wire:model="search">

            <!-- RESULTS -->
            <div class="list-group">
                @forelse($liturgy as $item)
                    <div class="list-group-item d-flex justify-content-between align-items-start">

                        <div>
                            <div class="fw-bold">{{ $item->title }}</div>
                        </div>

                        <button wire:click="addLiturgy({{ $item->id }})"
                                class="btn btn-sm btn-outline-primary">
                            + Plan
                        </button>

                    </div>
                @empty
                    <div class="text-muted p-2">No liturgy found</div>
                @endforelse
            </div>

        </div>

        <!-- =========================
             ORDER TAB
        ========================== -->
        <div class="tab-pane fade" id="orderTab">

            <div class="card">
                <div class="card-header py-2">
                    Final Order of Service
                </div>

                <div class="card-body p-2">

                    @forelse($orderItems as $item)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-1">

                            <div>
                                <strong>{{ $item->title }}</strong>
                                <br>
                                <small class="text-muted">
                                    {{ $item->content_type }}
                                </small>
                            </div>

                            <button wire:click="removeOrder({{ $item->id }})"
                                    class="btn btn-sm btn-outline-danger">
                                ✕
                            </button>

                        </div>
                    @empty
                        <div class="text-muted small text-center p-3">
                            No items in order yet
                        </div>
                    @endforelse

                </div>
            </div>

        </div>

    </div>

    <!-- LOADING INDICATOR -->
    <div wire:loading class="text-muted small mt-2">
        Updating...
    </div>

</div>