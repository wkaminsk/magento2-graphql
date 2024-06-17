<?php

namespace Riskified\DeciderGraphQl\Plugin;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\QuoteIdToMaskedQuoteIdInterface;

class CartTokenResolver
{
    private SessionManager $sessionManager;

    /**
     * @var QuoteIdToMaskedQuoteIdInterface
     */
    private QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId;

    public function __construct(
        SessionManager $sessionManager,
        QuoteIdToMaskedQuoteIdInterface $quoteIdToMaskedId
    ) {
        $this->quoteIdToMaskedId = $quoteIdToMaskedId;
        $this->sessionManager = $sessionManager;
    }

    public function afterGetCartToken($subject, $result, $order): string
    {
        if (is_null($order->getRiskifiedCartToken())) {
            $cartToken = null;

            try {
                $cartToken = $this->quoteIdToMaskedId->execute($order->getQuoteId());
            } catch (NoSuchEntityException $e) {
            }

            if (!$cartToken) {
                $cartToken = $this->sessionManager->getSessionId();
            }

            //save card_token into db
            $order->setRiskifiedCartToken($cartToken);
        } else {
            $cartToken = $order->getRiskifiedCartToken();
        }

        return $cartToken;
    }
}
