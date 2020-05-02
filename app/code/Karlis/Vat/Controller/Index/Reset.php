<?php declare(strict_types=1);


namespace Karlis\Vat\Controller\Index;


use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;

class Reset extends AbstractJsonResponse
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Reset constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context, $resultPageFactory, $jsonHelper);

        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        try {
            // Enable tax back
            $this->checkoutSession->setData(\Karlis\Vat\Helper\Data::CHECKOUT_SESSION_EXCLUDE_TAX_KEY, 0);

            $this->jsonResponse(['message' => 'Company code removed from the quote. TAX applied to order.']);
        } catch (LocalizedException $e) {
            $this->jsonResponse('', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->jsonResponse('', $e->getMessage());
        }
    }
}
