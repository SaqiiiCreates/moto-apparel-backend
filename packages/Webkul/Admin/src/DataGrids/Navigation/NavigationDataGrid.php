<?php

namespace Webkul\Admin\DataGrids\Navigation;

use Illuminate\Support\Facades\DB;
use Webkul\DataGrid\DataGrid;

class NavigationDataGrid extends DataGrid
{
    public function prepareQueryBuilder()
    {
        return DB::table('navigation_menus')
            ->select(
                'id',
                'title',
                'sort_order',
                'created_at'
            );
    }

    public function addColumns()
    {
        $this->addColumn([
            'index'      => 'id',
            'label'      => 'ID',
            'type'       => 'number',
            'searchable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'title',
            'label'      => 'Title',
            'type'       => 'string',
            'searchable' => true,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'sort_order',
            'label'      => 'Sort Order',
            'type'       => 'number',
            'searchable' => false,
            'sortable'   => true,
        ]);

        $this->addColumn([
            'index'      => 'created_at',
            'label'      => 'Created At',
            'type'       => 'date',
            'sortable'   => true,
        ]);
    }

    public function prepareActions()
    {
        $this->addAction([
            'title'  => 'Edit',
            'method' => 'GET',
            'route'  => 'admin.navigation.edit',
            'icon'   => 'icon-edit',
        ]);

        $this->addAction([
            'title'  => 'Delete',
            'method' => 'DELETE',
            'route'  => 'admin.navigation.delete',
            'icon'   => 'icon-delete',
        ]);
    }
}