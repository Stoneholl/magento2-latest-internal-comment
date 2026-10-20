<?php

declare(strict_types=1);

namespace Stoneholl\LatestInternalComment\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Block\Adminhtml\Order\View\Tab\Info;
use Stoneholl\LatestInternalComment\Model\Summary as SummaryModel;

class Summary extends Template
{
    /** @var SummaryModel */
    private $summary;
    /** @var int */
    private $currentOrderId = 0;

    public function __construct(Context $context, SummaryModel $summary, array $data = [])
    {
        $this->summary = $summary;
        parent::__construct($context, $data);
    }

    public function getSummaryState(): array
    {
        $this->currentOrderId = 0;
        if (!$this->summary->isAllowed()) {
            return ['status' => 'hidden'];
        }
        $info = $this->getLayout()->getBlock('order_tab_info');
        if (!$info instanceof Info) {
            return ['status' => 'unavailable'];
        }
        try {
            $order = $info->getOrder();
        } catch (\Magento\Framework\Exception\LocalizedException $exception) {
            $this->_logger->warning('STONEHOLL_LATEST_INTERNAL_COMMENT_CURRENT_ORDER_UNAVAILABLE');
            return ['status' => 'unavailable'];
        }
        if (!$order instanceof OrderInterface) {
            return ['status' => 'unavailable'];
        }
        $this->currentOrderId = (int) $order->getEntityId();
        return $this->summary->getState($order);
    }

    public function getOutputEscaper(): \Magento\Framework\Escaper
    {
        return $this->_escaper;
    }

    public function getRefreshUrl(): string
    {
        if ($this->currentOrderId <= 0 || !$this->summary->isAllowed()) {
            return '';
        }
        // Whitelist the loaded order ID; never propagate request emails, filters or redirect parameters.
        return $this->getUrl('sales/order/view', ['order_id' => $this->currentOrderId]);
    }

    public function getSourceDate(?string $timestamp): string
    {
        if (!$timestamp || !preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/D', $timestamp)) {
            return (string) __('Timestamp unavailable');
        }
        return $this->formatDate($timestamp, \IntlDateFormatter::MEDIUM, true);
    }
}
