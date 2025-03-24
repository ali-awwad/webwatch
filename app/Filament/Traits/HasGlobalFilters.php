<?php

namespace App\Filament\Traits;

use App\Models\Variation;
use App\Models\Website;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\On;

trait HasGlobalFilters
{
    // Filter properties
    public ?array $divisions = [];
    public ?array $companies = [];
    public ?array $hostings = [];
    public ?array $techStacks = [];
    public ?array $developerTeams = [];


    #[On('DivisionsFilterChanged')]
    public function handleDivisionsFilterChanged(array $divisions): void
    {
        $this->divisions = $divisions;
        //$this->updateChartData();
    }

    #[On('CompaniesFilterChanged')]
    public function handleCompaniesFilterChanged(array $companies): void
    {
        $this->companies = $companies;
        //$this->updateChartData();
    }

    #[On('HostingsFilterChanged')]
    public function handleHostingsFilterChanged(array $hostings): void
    {
        $this->hostings = $hostings;
        //$this->updateChartData();
    }

    #[On('TechStacksFilterChanged')]
    public function handleTechStacksFilterChanged(array $techStacks): void
    {
        $this->techStacks = $techStacks;
        //$this->updateChartData();
    }

    #[On('DeveloperTeamsFilterChanged')]
    public function handleDeveloperTeamsFilterChanged(array $developerTeams): void
    {
        $this->developerTeams = $developerTeams;
        //$this->updateChartData();
    }


    public function getVariationsQuery(bool $onlyMain = true): Builder
    {
        $query = Variation::query()
            ->when($onlyMain, function ($query) {
                $query->where('variations.is_main', true);
            })
            ->join('websites', 'variations.website_id', '=', 'websites.id')
            ->join('companies', 'websites.company_id', '=', 'companies.id')
            ->join('divisions', 'companies.division_id', '=', 'divisions.id')
            ->join('hostings', 'variations.hosting_id', '=', 'hostings.id')
            ->join('developer_teams', 'websites.developer_team_id', '=', 'developer_teams.id')
            ->join('tech_stack_website', 'websites.id', '=', 'tech_stack_website.website_id')
            ->join('tech_stacks', 'tech_stack_website.tech_stack_id', '=', 'tech_stacks.id');

        if ($this->divisions) {
            $query->whereIn('companies.division_id', $this->divisions);
        }

        if ($this->companies) {
            $query->whereIn('companies.id', $this->companies);
        }

        if ($this->developerTeams) {
            $query->whereIn('developer_teams.id', $this->developerTeams);
        }

        if ($this->hostings) {
            $query->whereIn('hostings.id', $this->hostings);
        }

        if ($this->techStacks) {
            $query->whereIn('tech_stacks.id', $this->techStacks);
        }

        if ($this->developerTeams) {
            $query->whereIn('developer_teams.id', $this->developerTeams);
        }



        return $query;
    }
}
