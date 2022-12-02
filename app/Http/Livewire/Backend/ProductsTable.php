<?php

namespace App\Http\Livewire\Backend;

use App\Domains\Auth\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Rappasoft\LaravelLivewireTables\DataTableComponent;
use Rappasoft\LaravelLivewireTables\Views\Column;
use Rappasoft\LaravelLivewireTables\Views\Filter;

class ProductsTable extends DataTableComponent
{
    /**
     * @return Builder
     */
    public function query(): Builder
    {
        return Product::whereNotNull('publish_date')
            ->when($this->getFilter('search'), fn ($query, $term) => $query->search($term))
            ->when($this->getFilter('user'), fn ($query, $user) => $query->searchUser($user));
    }

    /**
     * @return array
     */
    public function filters(): array
    {
        $users = User::select('email')->get()->pluck('email')->toArray();
        $userFilter = [
            '' => 'Any',
        ];
        foreach ($users as $user) {
            $userFilter[$user] = $user;
        }

        return [
            'user' => Filter::make('User')->select($userFilter),
        ];
    }


    public function columns(): array
    {
        return [
            Column::make(__('Ebay Id'))->sortable(),
            Column::make(__('Ebay Url')),
            Column::make(__('Description')),
            Column::make(__('Publisher'))->sortable(),
            Column::make(__('Publish Date'))->sortable(),
            // Column::make(__('Actions')),
        ];
    }

    public function rowView(): string
    {
        return 'backend.products.includes.row';
    }
}
