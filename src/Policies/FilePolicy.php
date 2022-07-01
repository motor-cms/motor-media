<?php

namespace Motor\Media\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Motor\Admin\Models\User;
use Motor\Media\Models\File;

class FilePolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  string  $ability
     * @return void|bool
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('SuperAdmin')) {
            return true;
        }
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('files.read');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Motor\Media\Models\File  $file
     * @return mixed
     */
    public function view(User $user, File $file)
    {
        return $user->hasPermissionTo('files.read');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('files.write');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Motor\Media\Models\File  $file
     * @return mixed
     */
    public function update(User $user, File $file)
    {
        return $user->hasPermissionTo('files.write');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Motor\Media\Models\File  $file
     * @return mixed
     */
    public function delete(User $user, File $file)
    {
        return $user->hasPermissionTo('files.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Motor\Media\Models\File  $file
     * @return mixed
     */
    public function restore(User $user, File $file)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \Motor\Admin\Models\User  $user
     * @param  \Motor\Media\Models\File  $file
     * @return mixed
     */
    public function forceDelete(User $user, File $file)
    {
        //
    }
}
