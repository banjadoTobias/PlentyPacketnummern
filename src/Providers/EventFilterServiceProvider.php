<?php
namespace EventFilter\Providers;

/********************************************************************
 * File:    EventFilterServiceProvider.php
 * Author:  Thorsten Laing ( laing@web.de )
 * Date:    04.04.17
 *******************************************************************/

use Plenty\Modules\EventProcedures\Services\Entries\ProcedureEntry;
use Plenty\Modules\EventProcedures\Services\EventProceduresService;
use Plenty\Plugin\Log\Loggable;
use Plenty\Plugin\ServiceProvider;

class EventFilterServiceProvider extends ServiceProvider
{

    use Loggable;

    public function register(){}


    /**
     * @param EventProceduresService $eventProceduresService
     * @see   ProcedureEntry::PROCEDURE_GROUP_ORDER
     */
    public function boot(EventProceduresService $eventProceduresService){

        $eventProceduresService->registerProcedure('syncPackagenumbers' , ProcedureEntry::PROCEDURE_GROUP_ORDER, [
            'de' => 'Sycronisiere SendungsNr. von Lieferauftrag',
            'en' => 'Sync Packagenumber from deliveryorder',

        ], 'EventFilter\\Procedures\\setPackageNumberInMainOrder@Procedure');

    }
}
