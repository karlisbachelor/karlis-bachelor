<?php declare(strict_types=1);


namespace Karlis\Vat\Controller\Index;


use Magento\Framework\Exception\LocalizedException;

class ValidateAndApply extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Karlis\Vat\Helper\Data
     */
    protected $karlisHelper;

    /**
     * @var \Karlis\Vat\Model\DataGov
     */
    protected $dataGov;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Karlis\Vat\Helper\Data $karlisHelper
     * @param \Karlis\Vat\Model\DataGov $dataGov
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Karlis\Vat\Helper\Data $karlisHelper,
        \Karlis\Vat\Model\DataGov $dataGov
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->karlisHelper = $karlisHelper;
        $this->dataGov = $dataGov;
    }

    /**
     * Execute view action
     */
    public function execute()
    {
        try {
            $sepa = $this->getRequest()->getParam('sepa');
            if (!$sepa) {
                throw new LocalizedException(__('SEPA value is missing! Please try again.'));
            }

            // Set data for API call
            $this->dataGov
                ->setResourceId($this->karlisHelper->getResourceId())
                ->setSepa($sepa);

            // Run API calll
            $this->dataGov->run();

            // Get response
            $response = $this->dataGov->getResponse();

            $this->jsonResponse(['api_response' => $response]);
        } catch (LocalizedException $e) {
            $this->jsonResponse('', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->jsonResponse('', $e->getMessage());
        }
    }

    /**
     * JSON response builder.
     *
     * @param $data
     * @param string $error
     */
    private function jsonResponse($data = '', string $error = '')
    {
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($this->getResponseData($data, $error))
        );
    }

    /**
     * Returns response data.
     *
     * @param $data
     * @param string $error
     * @return array
     */
    private function getResponseData($data = '', string $error = ''): array
    {
        $response = ['data' => $data, 'success' => true];

        if (!empty($error)) {
            $response = [
                'success' => false,
                'error_message' => $error,
            ];
        }

        return $response;
    }
}
