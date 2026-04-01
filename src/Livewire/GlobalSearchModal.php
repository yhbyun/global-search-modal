<?php

namespace CharrafiMed\GlobalSearchModal\Livewire;

use App\Filament\Resources\CompanyResource;
use App\Utils\SearchHelper;
use CharrafiMed\GlobalSearchModal\Utils\Highlighter;
use Filament\Facades\Filament;
use Filament\GlobalSearch\GlobalSearchResults;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

#[AllowDynamicProperties]
class GlobalSearchModal extends Component
{
    public ?string $search = '';

    #[Computed()]
    public function getConfigs()
    {
        return filament('global-search-modal');
    }

    #[Computed()]
    public function getPanelId()
    {
        return filament()->getCurrentPanel()->getId();
    }

    public function getResults(): ?GlobalSearchResults
    {
        if (! $this->hasTenantOrIsAuthenticated()) {
            return null;
        }

        // Early return if the search is empty
        $search = trim($this->search);
        if (empty($search)) {
            return GlobalSearchResults::make();
        }

        $results = Filament::getGlobalSearchProvider()->getResults($search);

        if (! $results || ! $this->getConfigs()->isMustHighlightQueryMatches()) {
            return $results;
        }

        $classes = $this->getConfigs()->getHighlightQueryClasses() ?? 'text-primary-500 font-semibold hover:underline';
        $styles = $this->getConfigs()->getHighlightQueryStyles() ?? '';

        // Apply highlighting to search results
        $companyCategory = CompanyResource::getPluralModelLabel();
        // Remove corporate suffixes but keep spaces for word-level highlighting
        $companyPattern = str_replace(' ', "\x00", $search);
        $companyPattern = SearchHelper::normalizeCompanySearchTerm($companyPattern);
        $companyPattern = str_replace("\x00", ' ', $companyPattern);

        foreach ($results->getCategories() as $category => &$categoryResults) {
            $pattern = $category === $companyCategory ? $companyPattern : $search;

            foreach ($categoryResults as &$result) {
                $result->highlightedTitle = Highlighter::make(
                    text: $result->title,
                    pattern: $pattern,
                    styles: $styles,
                    classes: $classes,
                    splitWords: true
                );
            }
        }

        return $results;
    }

    public function saveRecentSearch(string $search)
    {
        $search = trim($search);
        if (! empty($search)) {
            $this->dispatch('add-to-recent-searches', search: $search);
        }
    }

    public function setSearch(string $term)
    {
        $this->search = $term;
    }

    protected function hasTenantOrIsAuthenticated(): bool
    {
        return Filament::getTenant() || auth()->check();
    }

    public function render(): View
    {
        return view('global-search-modal::components.dialog', [
            'results' => $this->getResults(),
        ]);
    }
}
