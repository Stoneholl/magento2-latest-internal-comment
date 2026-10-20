<?php

declare(strict_types=1);

namespace Stoneholl\LatestInternalComment\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Sql\Expression;

/** Internal reader. Only Summary may call it after configuration and ACL checks. */
class HistoryReader
{
    /** @var ResourceConnection */
    private $resource;

    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Read at most 201 IDs and one comment within the native TEXT-size budget.
     *
     * @param int $orderId
     * @return array
     */
    public function read(int $orderId): array
    {
        if ($orderId <= 0) {
            throw new \InvalidArgumentException('A persisted order is required.');
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('sales_order_status_history');
        $ids = $connection->fetchCol($connection->select()->from($table, ['entity_id'])
            ->where('parent_id = ?', $orderId)->order('entity_id DESC')->limit(201));
        $limited = count($ids) > 200;
        $ids = array_slice($ids, 0, 200);
        if (!$ids) {
            return ['limited' => false, 'searched' => 0, 'row' => null];
        }
        // TRIM handles ASCII spaces; REPLACE removes tab, CR and LF for eligibility only.
        $text = "TRIM(REPLACE(REPLACE(REPLACE(comment, CHAR(9), ''), CHAR(13), ''), CHAR(10), ''))";
        $select = $connection->select()->from($table, [
            'entity_id', 'created_at',
            'text' => new Expression(
                'CASE WHEN OCTET_LENGTH(comment) <= 65535 THEN comment ELSE SUBSTRING(comment, 1, 201) END'
            ),
            'full_available' => new Expression('OCTET_LENGTH(comment) <= 65535')
        ])->where('parent_id = ?', $orderId)->where('entity_id IN (?)', $ids)
            ->where('CAST(entity_name AS BINARY) = CAST(? AS BINARY)', 'order')
            ->where('is_visible_on_front = ?', 0)->where('is_customer_notified = ?', 0)
            ->where($text . " <> ''")->order('entity_id DESC')->limit(1);
        // Reapply every eligibility predicate: changed/deleted rows must not reuse a prior excerpt.
        $row = $connection->fetchRow($select);
        return ['limited' => $limited, 'searched' => count($ids), 'row' => $row ?: null];
    }
}
