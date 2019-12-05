<?php namespace EventFilter\Procedures;

use Plenty\Modules\Comment\Contracts\CommentRepositoryContract;
use Plenty\Modules\EventProcedures\Events\EventProceduresTriggered;
use Plenty\Modules\Order\Contracts\OrderRepositoryContract;
use Plenty\Modules\Order\Models\Order;
use Plenty\Modules\Order\Shipping\Package\Contracts\OrderShippingPackageRepositoryContract;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Log\Loggable;


class setPackageNumberInMainOrder{

    use Loggable;

    /** @var OrderRepositoryContract $OrderRepo */
    private $OrderRepo;

    /** @var ConfigRepository $Config */
    private $Config;

    /**
     * setPackageNumberInMainOrder constructor.
     * @param OrderRepositoryContract $orderRepositoryContract
     * @param ConfigRepository $Config
     */
    public function __construct(OrderRepositoryContract $orderRepositoryContract, ConfigRepository $Config){
        $this->OrderRepo = $orderRepositoryContract;
        $this->Config = $Config;
    }


    /**
     * Entrypoint for Procedure.
     * @param EventProceduresTriggered $eventTriggered
     * @return bool
     */
    public function Procedure(EventProceduresTriggered $eventTriggered){

        $Order = $eventTriggered->getOrder();

        $deliveryOrder = $Order;
        $originOrder   = $this->getOriginOrder($Order);

        $this->getLogger('Sync Packagenumber')->error($deliveryOrder->id . ' ->  ' . $originOrder->id );

        $packageNumers = $this->OrderRepo->getPackageNumbers($deliveryOrder->id);

        $this->saveShippingInformation($originOrder->id, $packageNumers[0]);

        $this->setOrderNotice($originOrder->id, 'Aus Lieferauftrag ' $OrderRepo 'TrackingNR:'.$packageNumers[0].' importiert' $originOrder);

        return true;
    }


    /**
     * @param $Order
     * @return bool|Order
     */
    private function getOriginOrder($Order){

        foreach ($Order->orderReferences as $reference){

            if($reference->referenceType === 'parent'){

                return $this->OrderRepo->findOrderById($reference->originOrderId);
            }
        }

        return false;
    }


    /**
     * Saves the shipping information
     *
     * @param $orderId
     * @param $shipmentNumber
     */
    private function saveShippingInformation($orderId,$shipmentNumber)
    {

        /** @var OrderShippingPackageRepositoryContract $OrderShippingPackageRepositoryContract */
        $OrderShippingPackageRepositoryContract = pluginApp(OrderShippingPackageRepositoryContract::class);

        $packageId = $this->Config->get('EventFilter.packageid');

        $OrderShippingPackageRepositoryContract->createOrderShippingPackage($orderId,[
            'packageId' => $packageId,
            'packageNumber' => $shipmentNumber,
            'packageType' => 26
        ]);
    }


    /**
     * @param $OrderID
     * @param $notice
     * @return \Plenty\Modules\Comment\Models\Comment
     */
    private function setOrderNotice($OrderID, $notice){

        /** @var CommentRepositoryContract $commentRepositoryContract */
        $commentRepositoryContract = pluginApp(CommentRepositoryContract::class);

        $userId = $this->Config->get('EventFilter.useridfornotice');

        return $commentRepositoryContract->createComment([
            'referenceType'  => 'order',
            'userId'         => $userId,
            'referenceValue' => $OrderID,
            'text'           => $notice,
            'isVisibleForContact' => false
        ]);

    }
}
