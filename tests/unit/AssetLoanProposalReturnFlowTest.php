<?php

use App\Controllers\AssetLoanProposalController;
use App\Models\AssetLoanProposalItemModel;
use CodeIgniter\Test\CIUnitTestCase;

final class AssetLoanProposalReturnFlowTest extends CIUnitTestCase
{
    public function testReturnWorkflowIsAvailable(): void
    {
        $controller = new AssetLoanProposalController();
        $model = new AssetLoanProposalItemModel();

        $this->assertTrue(method_exists($controller, 'returnPage'));
        $this->assertTrue(method_exists($controller, 'saveReturnStatus'));
        $this->assertTrue(method_exists($model, 'markReturned'));
        $this->assertTrue(method_exists($model, 'hasAllReturned'));
    }
}
