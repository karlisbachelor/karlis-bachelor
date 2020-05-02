<?php declare(strict_types=1);


namespace Karlis\Vat\Observer\Sales;


class QuoteCollectTotalsBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * QuoteCollectTotalsBefore constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        /** If checkout session has this data, exclude tax */
        if ($this->checkoutSession->getData(\Karlis\Vat\Helper\Data::CHECKOUT_SESSION_EXCLUDE_TAX_KEY)) {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getData('quote');

            foreach ($quote->getAllItems() as &$item) {
                $product = $item->getProduct();
                // Set tax class - 0 (0 tax class has 0%)
                $product->setTaxClassId(0);
            }
        }
    }
}
