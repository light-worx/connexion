<div>

    <!-- TABS -->
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <button type="button"
                    class="nav-link @if($activeTab==='songsTab') active @endif"
                    wire:click="setTab('songsTab')">
                Songs
            </button>
        </li>
        <li class="nav-item">
            <button type="button"
                    class="nav-link @if($activeTab==='liturgyTab') active @endif"
                    wire:click="setTab('liturgyTab')">
                Liturgy
            </button>
        </li>
        <li class="nav-item">
            <button type="button"
                    class="nav-link @if($activeTab==='orderTab') active @endif"
                    wire:click="setTab('orderTab')">
                Order
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- SEARCH -->
        <!-- =========================
             SONGS TAB
        ========================== -->
        <div class="tab-pane fade {{ $activeTab === 'songsTab' ? 'show active' : '' }}" id="songsTab">
            <!-- ONLY inside #songsTab -->
            <div class="mb-2">
                <input type="text" class="form-control mb-2" placeholder="Search songs..." wire:model.live="songSearch">
                <div class="d-flex gap-2">

                    <div class="form-check">
                        <input type="checkbox" value="hymn" wire:model.live="types">
                        <label class="form-check-label small">Hymn</label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" value="contemporary" wire:model.live="types">
                        <label class="form-check-label small">Contemporary</label>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" value="archive" wire:model.live="types">
                        <label class="form-check-label small">Archive</label>
                    </div>

                </div>

            </div>

            <!-- PLANNED SONGS -->
            <div class="accordion mb-3" id="songsAccordion">

                <div class="accordion-item">

                    <h2 class="accordion-header" id="songsHeading">
                        <button class="accordion-button collapsed py-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#songsCollapse"
                                aria-expanded="false">

                            Planned Songs

                            <span class="badge bg-primary ms-2">
                                {{ count($plannedSongs) }}
                            </span>

                        </button>
                    </h2>

                    <div id="songsCollapse"
                        class="accordion-collapse collapse"
                        data-bs-parent="#songsAccordion">

                        <div class="accordion-body p-2">

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

                </div>

            </div>

            <!-- RESULTS -->
            <div class="list-group">
                @forelse($songs as $song)
                    <div class="list-group-item d-flex justify-content-between align-items-start">

                        <div>
                            <div class="fw-bold">{{ $song->title }}</div>

                            <small class="text-muted">
                                {{ ucfirst($song->musictype ?? '') }}
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
        <div class="tab-pane fade {{ $activeTab === 'liturgyTab' ? 'show active' : '' }}" id="liturgyTab">
            <!-- SEARCH -->
            <input type="text" class="form-control mb-2" placeholder="Search liturgy..." wire:model.live="liturgySearch">
            <!-- PLANNED LITURGY -->
            <div class="accordion mb-3" id="liturgyAccordion">

                <div class="accordion-item">

                    <h2 class="accordion-header" id="liturgyHeading">
                        <button class="accordion-button collapsed py-2"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#liturgyCollapse"
                                aria-expanded="false">

                            Planned Liturgy

                            <span class="badge bg-primary ms-2">
                                {{ count($plannedLiturgy) }}
                            </span>

                        </button>
                    </h2>

                    <div id="liturgyCollapse"
                        class="accordion-collapse collapse"
                        data-bs-parent="#liturgyAccordion">

                        <div class="accordion-body p-2">

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
                </div>
            </div>

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
        <div class="tab-pane fade {{ $activeTab === 'orderTab' ? 'show active' : '' }}" id="orderTab">

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