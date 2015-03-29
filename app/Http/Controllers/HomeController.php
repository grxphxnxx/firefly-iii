<?php namespace FireflyIII\Http\Controllers;

use Carbon\Carbon;
use FireflyIII\Repositories\Account\AccountRepositoryInterface;
use Input;
use Preferences;
use Session;

/**
 * Class HomeController
 *
 * @package FireflyIII\Http\Controllers
 */
class HomeController extends Controller
{

    public function dateRange()
    {
        $start = new Carbon(Input::get('start'));
        $end   = new Carbon(Input::get('end'));

        $diff = $start->diffInDays($end);

        if ($diff > 50) {
            Session::flash('warning', $diff . ' days of data may take a while to load.');
        }

        Session::put('start', $start);
        Session::put('end', $end);
    }

    /**
     * @return \Illuminate\View\View
     */
    public function index(AccountRepositoryInterface $repository)
    {

        $count         = $repository->countAssetAccounts();
        $title         = 'Firefly';
        $subTitle      = 'What\'s playing?';
        $mainTitleIcon = 'fa-fire';
        $transactions  = [];
        $frontPage     = Preferences::get('frontPageAccounts', []);
        $start         = Session::get('start', Carbon::now()->startOfMonth());
        $end           = Session::get('end', Carbon::now()->endOfMonth());
        $accounts      = $repository->getFrontpageAccounts($frontPage);
        $savings       = $repository->getSavingsAccounts();

        foreach ($accounts as $account) {
            $set = $repository->getFrontpageTransactions($account, $start, $end);
            if (count($set) > 0) {
                $transactions[] = [$set, $account];
            }
        }

        return view('index', compact('count', 'title', 'savings', 'subTitle', 'mainTitleIcon', 'transactions'));
    }


}