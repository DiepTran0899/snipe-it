<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\User;


/**
 * This controller handles all actions related to the Admin Dashboard
 * for the Snipe-IT Asset Management application.
 *
 * @author A. Gianotto <snipe@snipe.net>
 * @version v1.0
 */
class DashboardController extends Controller
{
    /**
     * Check authorization and display admin dashboard, otherwise display
     * the user's checked-out assets.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function index() : View | RedirectResponse
    {
        // Show the page
        if (auth()->user()->hasAccess('admin')) {
            $asset_stats = null;

            $counts['asset'] = \App\Models\Asset::count();
            $counts['accessory'] = \App\Models\Accessory::count();
            $counts['license'] = \App\Models\License::assetcount();
            $counts['consumable'] = \App\Models\Consumable::count();
            $counts['component'] = \App\Models\Component::count();
            $counts['user'] = \App\Models\Company::scopeCompanyables(auth()->user())->count();
            $counts['grand_total'] = $counts['asset'] + $counts['accessory'] + $counts['license'] + $counts['consumable'];

            if ((! file_exists(storage_path().'/oauth-private.key')) || (! file_exists(storage_path().'/oauth-public.key'))) {
                Artisan::call('migrate', ['--force' => true]);
                Artisan::call('passport:install', ['--no-interaction' => true]);
            }

            return view('dashboard')->with('asset_stats', $asset_stats)->with('counts', $counts);
        } else {
            Session::reflash();

            // Redirect to the profile page
            return redirect()->intended('account/view-assets');
        }
    }

    /**
     * Display the dynamic dashboard view.
     */
    public function custom() : View
    {
        $statuses = Statuslabel::orderBy('name')->get(['id', 'name'])->map->only('id','name');
        $models = AssetModel::orderBy('name')->get(['id', 'name'])->map->only('id','name');
        $locations = Location::orderBy('name')->get(['id', 'name'])->map->only('id','name');
        $users = User::orderBy('first_name')->get()->map(function ($u) {
            return ['id' => $u->id, 'name' => trim($u->first_name . ' ' . $u->last_name)];
        });

        return view('dashboard_custom', compact('statuses', 'models', 'locations', 'users'));
    }

    /**
     * Provide asset data for the custom dashboard.
     */
    public function customData() : JsonResponse
    {
        $assets = Asset::with(['assetstatus', 'model', 'location', 'assignedTo'])
            ->orderBy('name')
            ->limit(1000)
            ->get();

        $data = $assets->map(function ($a) {
            return [
                'name' => $a->name,
                'serial' => $a->serial,
                'status' => optional($a->assetstatus)->name,
                'model' => optional($a->model)->name,
                'location' => optional($a->location)->name,
                'user' => optional($a->assignedTo)->name ?? optional($a->assignedTo)->first_name,
                'updated_at' => optional($a->updated_at)->toDateString(),
            ];
        });

        return response()->json($data);
    }
}
