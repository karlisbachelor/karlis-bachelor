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
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Karlis\Vat\Helper\Data $karlisHelper
     * @param \Karlis\Vat\Model\DataGov $dataGov
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Psr\Log\LoggerInterface $logger,
        \Karlis\Vat\Helper\Data $karlisHelper,
        \Karlis\Vat\Model\DataGov $dataGov,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->logger = $logger;
        $this->karlisHelper = $karlisHelper;
        $this->dataGov = $dataGov;
        $this->checkoutSession = $checkoutSession;
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
            $response = $this->jsonHelper->jsonDecode($this->dataGov->getResponse());

            // Check if response not empty and include all necessary fields
            if (!$this->validateResponse($response)) {
                throw new LocalizedException(__('The response from data.gov.lv is wrong.'));
            }

            // Default values if SEPA is invalid
            $jsonResponse = [
                'sepaValid' => false,
                'message' => __('Your Registration Code is invalid!')
            ];
            // Default session value if SEPA is invalid
            $this->checkoutSession->setData(\Karlis\Vat\Helper\Data::CHECKOUT_SESSION_EXCLUDE_TAX_KEY, 0);

            // Records array in response contains all founded companies with current SEPA
            // If count of records EQUAL to one, then we found company with current SEPA
            // If count of records NOT EQUAL one, then we can't found company with current SEPA
            if (count($response['result']['records']) == 1) {
                $this->checkoutSession->setData(\Karlis\Vat\Helper\Data::CHECKOUT_SESSION_EXCLUDE_TAX_KEY, 1);

                $jsonResponse = [
                    'sepaValid' => true,
                    'message' => __('Your Registration Code is valid!')
                ];
            }

            $this->jsonResponse($jsonResponse);
        } catch (LocalizedException $e) {
            $this->jsonResponse('', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->jsonResponse('', $e->getMessage());
        }
    }

    /**
     * @param array $response
     *
     * @return bool
     */
    protected function validateResponse($response)
    {
        if (!count($response)) {
            return false;
        } elseif (!isset($response['success']) || !$response['success']) {
            return false;
        } elseif (!isset($response['result']) || !isset($response['result']['records'])) {
            return false;
        }

        return true;
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
