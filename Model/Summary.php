<?php

declare(strict_types=1);

namespace Stoneholl\LatestInternalComment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

/** Request-local Admin presentation. No text is persisted, cached, logged or sent to Connector. */
class Summary
{
    /** @var ScopeConfigInterface */
    private $config;
    /** @var AuthorizationInterface */
    private $authorization;
    /** @var HistoryReader */
    private $reader;
    /** @var Preview */
    private $preview;
    /** @var LoggerInterface */
    private $logger;
    /** @var array */
    private $states = [];

    public function __construct(
        ScopeConfigInterface $config,
        AuthorizationInterface $authorization,
        HistoryReader $reader,
        Preview $preview,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->authorization = $authorization;
        $this->reader = $reader;
        $this->preview = $preview;
        $this->logger = $logger;
    }

    public function isAllowed(): bool
    {
        return $this->config->isSetFlag('stoneholl_latest_internal_comment/general/enabled')
            && $this->authorization->isAllowed('Magento_Sales::actions_view')
            && $this->authorization->isAllowed('Stoneholl_LatestInternalComment::view');
    }

    public function getState(OrderInterface $order): array
    {
        if (!$this->isAllowed()) {
            return ['status' => 'hidden'];
        }
        $orderId = (int) $order->getEntityId();
        if ($orderId <= 0) {
            return ['status' => 'unavailable'];
        }
        if (isset($this->states[$orderId])) {
            return $this->states[$orderId];
        }
        try {
            $result = $this->reader->read($orderId);
            $row = $result['row'];
            $state = [
                'status' => $row ? 'ready' : 'empty',
                'limited' => $result['limited'],
                'searched' => $result['searched']
            ];
            if ($row) {
                $state['preview'] = $this->preview->format((string) $row['text']);
                $state['full_text'] = !empty($row['full_available']) ? (string) $row['text'] : null;
                $state['created_at'] = $row['created_at'];
            }
            return $this->states[$orderId] = $state;
        } catch (\Exception $exception) {
            // Exceptions/traces may contain query parameters and text; log a fixed code only.
            $this->logger->warning('STONEHOLL_LATEST_INTERNAL_COMMENT_READ_FAILED');
            return $this->states[$orderId] = ['status' => 'unavailable'];
        }
    }
}
