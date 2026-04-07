<x-worship pageName="Service page">
    <div class="container-fluid">

        <!-- HEADER -->
        <div class="mb-3 p-3 bg-light rounded">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">
                        {{ date('j-m-y', strtotime($service->servicedate)) }} {{ $service->servicetime }} 
                    </h5>
                </div>

                <div class="d-flex gap-2">
                    <a href="#" class="btn btn-outline-secondary btn-sm">Copy</a>
                    <a href="#" class="btn btn-outline-success btn-sm">Mark Final</a>
                    <button class="btn btn-primary btn-sm">Save</button>
                </div>
            </div>
        </div>

        <div class="row">

            <!-- LEFT PANEL -->
            <div class="col-md-5">

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-2" id="libraryTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#songs">
                            Songs
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#liturgy">
                            Liturgy
                        </button>
                    </li>
                </ul>

                <div class="tab-content">

                    <!-- SONGS TAB -->
                    <div class="tab-pane fade show active" id="songs">

                        @livewire('service-planner', ['service' => $service])

                    </div>

                    <!-- LITURGY TAB -->
                    <div class="tab-pane fade" id="liturgy">
                        <div class="p-3 text-muted">
                            Liturgy search goes here
                        </div>
                    </div>

                </div>

                <!-- POTENTIAL ITEMS -->
                <div class="card mt-3">
                    <div class="card-header py-2">
                        Potential Items
                    </div>

                    <div class="card-body p-2">
                        <div class="d-flex flex-wrap gap-2">

                            @forelse($candidates as $candidate)
                                <span class="badge bg-secondary d-flex align-items-center">
                                    {{ $candidate->item->title ?? 'Item' }}

                                    <form method="POST"
                                        action="{{ route('services.candidates.destroy', [$service, $candidate]) }}"
                                        class="ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn-close btn-close-white btn-sm"></button>
                                    </form>
                                </span>
                            @empty
                                <span class="text-muted">No items added yet</span>
                            @endforelse

                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL -->
            <div class="col-md-7">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Order of Service</h6>

                    <form method="POST" action="{{ url('services', $service) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-primary">
                            Add from Potential Items
                        </button>
                    </form>
                </div>

                @forelse($orderItems as $item)
                    <div class="card mb-2">
                        <div class="card-body p-2 d-flex justify-content-between align-items-center">

                            <div>
                                <strong>{{ $item->label ?? 'Item' }}</strong><br>
                                <small class="text-muted">
                                    {{ $item->item->title ?? '' }}
                                </small>
                            </div>

                            <div class="d-flex gap-1">

                                <!-- Move Up -->
                                <form method="POST" action="{{ route('services.order.moveUp', [$service, $item]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">↑</button>
                                </form>

                                <!-- Move Down -->
                                <form method="POST" action="{{ route('services.order.moveDown', [$service, $item]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary">↓</button>
                                </form>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('services.order.destroy', [$service, $item]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">✕</button>
                                </form>

                            </div>

                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted p-4 border rounded">
                        No order created yet
                    </div>
                @endforelse

            </div>

        </div>
    </div>
</x-worship>