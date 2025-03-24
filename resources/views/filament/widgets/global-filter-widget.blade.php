<x-filament::widget>
    <x-filament::section>
        <div class="space-y-4">
            <?php
            /**
            <h2 class="text-xl font-bold tracking-tight">Global Filters</h2>
            <p class="text-sm text-gray-500">Filter all dashboard widgets by selecting options below</p>
            */
            ?>
            
            <div class="grid grid-cols-5 gap-4">
                {{ $this->form }}
            </div>
        </div>
    </x-filament::section>
</x-filament::widget> 