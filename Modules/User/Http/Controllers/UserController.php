<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Routing\Controller;
use Modules\Saas\Entities\Package;
use Modules\User\Entities\User;
use Nwidart\Modules\Facades\Module;

class UserController extends Controller
{
    public function impersonate(Request $request, $id = null){
        if( ! isset($id) ){
            $manager = app('impersonate');

            if( ! $manager->isImpersonating() ) {
                return redirect()->back()->with('error', __('You are not impersonating anybody.'));
            }

            \Auth::user()->leaveImpersonation();

            return redirect()->route('settings.users.index')->with('success', __('Leave impersonate successfully'));
        }

        if( $id == \Auth::id() ){
            return redirect()->back()->with('error', __('You can\'t impersonate yourself.'));
        }

        $other_user = User::where('id', $id)->first();

        if( ! $other_user ){
            return redirect()->back()->with('error', __('Invalid User.'));
        }

        \Auth::user()->impersonate($other_user);
        return redirect()->route('dashboard')->with('success', __('Impersonate successfully'));
    }

     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = User::query();

        if ($request->filled('search')) {
            $data->where('name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
        }

        $data = $data->paginate(10);

        return view('user::users.index', compact(
            'data'
        ));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $packages = [];
        
        if (Module::find('Saas')) {
            $packages = Package::all();
        }

        return view('user::users.create', compact(
            'packages'
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:users',
            'password'        => 'required|string|min:6|same:password_confirmation',
            'package_ends_at' => 'nullable|date',
        ]);

        $request->request->add([
            'password' => Hash::make($request->password),
        ]);

        if (!$request->filled('is_admin')) {
            $request->request->add([
                'is_admin' => false,
            ]);
        } else {
            $request->request->add([
                'is_admin' => true,
            ]);
        }
        
        $user = User::create($request->all());

        return redirect()->route('settings.users.index')
            ->with('success', __('Created successfully'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {

        $packages = [];
        if (Module::find('Saas')) {
            $packages = Package::all();
        }
        return view('user::users.edit', compact(
            'user',
            'packages'
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'password'        => 'nullable|string|min:6|same:password_confirmation',
            'package_ends_at' => 'nullable|date',

        ]);

        if ($request->filled('password')) {
            $request->request->add([
                'password' => Hash::make($request->password),
            ]);
        } else {
            $request->request->remove('password');
        }

        if (!$request->filled('is_admin')) {
            $request->request->add([
                'is_admin' => false,
            ]);
        }
        
        $user->update($request->all());

        return redirect()->route('settings.users.edit', $user)
            ->with('success', __('Updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id == $user->id) {
            return redirect()->route('settings.users.index')
                ->with('error', __("You can't remove yourself."));
        }

        $user->delete();

        return redirect()->route('settings.users.index')
            ->with('success', __('Deleted successfully'));
    }

    public function accountSettings(Request $request)
    {

        $user = $request->user();
        return view('user::auth.profile', compact(
            'user'));
    }

    public function accountSettingsUpdate(Request $request)
    {

        $request->validate([
            'name'     => 'required|max:255',
            'password' => 'same:password_confirmation',
        ]);

        if ($request->filled('password')) {
            $request->request->add([
                'password' => Hash::make($request->password),
            ]);
        } else {
            $request->request->remove('password');
        }

        $request->user()->update($request->all());

        return redirect()->route('accountsettings.index')
            ->with('success', __('Updated successfully'));
    }
}
