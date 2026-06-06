{{--
    filament/pages/worship-planner/plan-modal-header.blade.php
    Variables: $plan (WorshipPlan), $group (WorshipSundayGroup)
--}}
<div class="flex items-center justify-between pb-2 mb-1 border-b border-gray-100 dark:border-gray-800">
    <div>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wider">
            {{ $group->service_date->format('l, j F Y') }}
            @if ($group->is_special_service)
                · <span class="text-primary-500">★ {{ $group->display_name }}</span>
            @endif
        </p>
        <h3 class="text-base font-bold text-gray-900 dark:text-white mt-0.5">
            {{ $plan->service_time }} Service
        </h3>
    </div>
    <span @class([
        'text-xs font-medium px-2.5 py-1 rounded-full',
        'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'                        => $plan->isDraft(),
        'bg-warning-100 text-warning-700 dark:bg-warning-900 dark:text-warning-300'             => $plan->isConfirmed(),
        'bg-success-100 text-success-700 dark:bg-success-900 dark:text-success-300'             => $plan->isPublished(),
    ])>
        {{ ucfirst($plan->status) }}
    </span>
</div>