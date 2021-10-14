<?php

use Heyday\GridFieldVersionedOrderableRows\GridFieldVersionedOrderableRows;
use SilverStripe\ORM\DB;


/**
 * Allows a where clause when using versioned orderable rows
 * Class GridFieldWhereableOrderableRows
 */
class GridFieldWhereableVersionedOrderableRows extends GridFieldVersionedOrderableRows
{
    /**
     * @var string
     */
    protected $maxWhereClause;

    /**
     * @param string $sortField
     * @param string $maxWhereClause
     */
    public function __construct($sortField = 'Sort', $maxWhereClause = '')
    {
        $this->sortField = $sortField;
        $this->maxWhereClause = $maxWhereClause;
    }

    /**
     * @param DataList $list
     */
    protected function populateSortValues(DataList $list)
    {
        $list = clone $list;
        $field = $this->getSortField();
        $table = $this->getSortTable($list);
        $clause = sprintf('"%s"."%s" = 0', $table, $this->getSortField());

        foreach ($list->where($clause)->column('ID') as $id) {
            if ($this->maxWhereClause) {
                $max = DB::query(sprintf('SELECT MAX("%s") + 1 FROM "%s" WHERE %s', $field, $table, $this->maxWhereClause));
            } else {
                $max = DB::query(sprintf('SELECT MAX("%s") + 1 FROM "%s"', $field, $table));
            }
            $max = $max->value();

            DB::query(
                sprintf(
                    'UPDATE "%s" SET "%s" = %d WHERE %s',
                    $table,
                    $field,
                    $max,
                    $this->getSortTableClauseForIds($list, $id)
                )
            );

            DB::query(
                sprintf(
                    'UPDATE "%s_Live" SET "%s" = %d WHERE %s',
                    $table,
                    $field,
                    $max,
                    $this->getSortTableClauseForIds($list, $id)
                )
            );
        }
    }
}
