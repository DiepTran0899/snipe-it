<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
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
     * Display a simplified custom dashboard with basic filtering.
     */
    public function custom(Request $request) : View
    {
        // eager load relations for status, model, location and assignee
        $query = \App\Models\Asset::with(['assetstatus', 'model', 'location', 'assignedTo']);

        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('user_id')) {
            $query->where('assigned_type', User::class)
                ->where('assigned_to', $request->user_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('serial', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('name')->limit(100)->get();

        $statusCounts = $assets->groupBy(fn ($a) => optional($a->assetstatus)->name ?? 'Unknown')
            ->map->count()
            ->toArray();

        $statuses = Statuslabel::orderBy('name')->pluck('name', 'id');
        $models = AssetModel::orderBy('name')->pluck('name', 'id');
        $locations = Location::orderBy('name')->pluck('name', 'id');
        $users = User::orderBy('first_name')->pluck('first_name', 'id');

        return view('dashboard_custom', compact(
            'assets',
            'statusCounts',
            'statuses',
            'models',
            'locations',
            'users'
        ));
    }
}
