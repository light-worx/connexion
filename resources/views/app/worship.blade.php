<x-worship pageName="Worship home">
    <div>
        @foreach($services as $date => $services)
            <div class="p-4 bg-white rounded-xl shadow">
                <div class="font-bold text-lg mb-2">
                    {{ \Carbon\Carbon::parse($date)->format('D j F Y') }}
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach($services as $service)
                        <a href="{{ url('service', $service) }}"
                        class="px-3 py-2 bg-primary-500 rounded-lg text-sm">
                            {{ $service->servicetime }}
                        </a>
                    @endforeach

                    <a href="{{ url('services/create', ['date' => $date]) }}"
                    class="px-3 py-2 bg-gray-200 rounded-lg text-sm">
                        + Add
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</x-worship>