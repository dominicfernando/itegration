<?php
namespace Dominic\Test\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;

class AddOptionToOrderObserver implements ObserverInterface
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * AddOptionToOrderObserver constructor.
     *
     * @param SerializerInterface $serializer
     */
    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * Add vendor details to additional option
     *
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var Quote $quote */
        $quote = $observer->getQuote();
        /** @var $orderInstance Order */
        $order = $observer->getOrder();
        $quoteItems = $this->getQuoteItemDetails($quote);
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $quoteItem = $quoteItems[$orderItem->getQuoteItemId()];
            $additionalOptions = $quoteItem->getOptionByCode('additional_options');
            if ($additionalOptions !== null) {
                $optionDetails = $additionalOptions->getValue();
                if (!empty($optionDetails)) {
                    $options = $orderItem->getProductOptions();
                    $options['additional_options'] = $this->serializer->unserialize($optionDetails);
                    $orderItem->setProductOptions($options);
                }
            }
        }

        return $this;
    }

    /**
     * Get quote details
     *
     * @param CartInterface $quote
     * @return array
     */
    public function getQuoteItemDetails(CartInterface $quote)
    {
        $quoteItems = [];
        foreach ($quote->getAllVisibleItems() as $quoteItem) {
            $quoteItems[$quoteItem->getId()] = $quoteItem;
        }
        return $quoteItems;
    }
}
