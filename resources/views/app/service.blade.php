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
            <div class="tab-pane fade show active" id="songs">
                @livewire('service-planner', ['service' => $service])
            </div>
        </div>
    </div>
</x-worship>