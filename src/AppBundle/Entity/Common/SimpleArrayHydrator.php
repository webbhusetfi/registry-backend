<?php
namespace AppBundle\Entity\Common;

use PDO;
use Doctrine\ORM\Internal\Hydration\AbstractHydrator;

/**
 * The SimpleArrayHydrator produces a result array with the columns grouped
 * by entity.
 *
 * @author Kim Wistbacka <kim@webbhuset.fi>
 */
class SimpleArrayHydrator extends AbstractHydrator
{
    /**
     * @var array
     */
    private $_idTemplate = [];

    /**
     * @var int
     */
    private $_resultKey = 0;

    /**
     * {@inheritdoc}
     */
    protected function prepare()
    {
        foreach ($this->_rsm->aliasMap as $dqlAlias => $className) {
            $this->_idTemplate[$dqlAlias] = '';
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function hydrateAllData()
    {
        $result = [];

        while ($data = $this->_stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->hydrateRowData($data, $result);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    protected function hydrateRowData(array $row, array &$result)
    {
        // 1) Initialize
        $id = $this->_idTemplate; // initialize the id-memory
        $nonemptyComponents = array();
        $rowData = $this->gatherRowData($row, $id, $nonemptyComponents);
        $resultKey = $this->_resultKey++;

        // 2) Now hydrate the data found in the current row.
        foreach ($rowData['data'] as $dqlAlias => $data) {
            if (isset($result[$resultKey])) {
                $result[$resultKey][$dqlAlias] = $data;
            } else {
                $result[$resultKey] = $data;
            }
        }

        // Append scalar values
        if (isset($rowData['scalars'])) {
            foreach ($rowData['scalars'] as $name => $value) {
                $result[$resultKey][$name] = $value;
            }
        }

        // Append new objects
        if (isset($rowData['newObjects'])) {
            $scalarCount = (isset($rowData['scalars'])? count($rowData['scalars']): 0);

            foreach ($rowData['newObjects'] as $objIndex => $newObject) {
                $class  = $newObject['class'];
                $args   = $newObject['args'];
                $obj    = $class->newInstanceArgs($args);

                $result[$resultKey][$objIndex] = $obj;
            }
        }
    }
}
