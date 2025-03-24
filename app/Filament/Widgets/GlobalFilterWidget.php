<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\DeveloperTeam;
use App\Models\Division;
use App\Models\Hosting;
use App\Models\TechStack;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Widgets\Widget;
use Illuminate\Contracts\View\View;

class GlobalFilterWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static ?int $sort = 1;
    protected static bool $isLazy = false;
    protected int|string|array $columnSpan = 'full';

    protected static string $view = 'filament.widgets.global-filter-widget';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->columns(4)
            ->schema([
                Forms\Components\Select::make('divisions')
                    ->options(Division::all()->pluck('name', 'id'))
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->dispatch('DivisionsFilterChanged', divisions: $state))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('companies')
                    ->options(Company::all()->pluck('name', 'id'))
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->dispatch('CompaniesFilterChanged', companies: $state))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('hostings')
                    ->options(Hosting::all()->pluck('name', 'id'))
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->dispatch('HostingsFilterChanged', hostings: $state))
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('techStacks')
                    ->options(TechStack::all()->pluck('name', 'id'))
                    ->multiple()
                    ->live()
                    ->afterStateUpdated(fn ($state) => $this->dispatch('TechStacksFilterChanged', techStacks: $state))
                    ->searchable()
                    ->preload(),
            ]);
    }
} 