<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     Roardom <roardom@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Livewire;

use App\Models\Apikey;
use App\Models\User;
use App\Traits\LivewireSort;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * @property \Illuminate\Contracts\Pagination\LengthAwarePaginator<Apikey> $apikeys
 */
class ApikeySearch extends Component
{
    use LivewireSort;
    use WithPagination;

    #[Url]
    public string $username = '';

    #[Url]
    public string $apikey = '';

    #[Url]
    public string $sortField = 'created_at';

    #[Url]
    public string $sortDirection = 'desc';

    #[Url]
    public int $perPage = 25;

    final public function updatedPage(): void
    {
        $this->dispatch('paginationChanged');
    }

    /**
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<Apikey>
     */
    #[Computed]
    final public function apikeys(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return Apikey::with([
            'user' => fn ($query) => $query->withTrashed()->with('group'),
        ])
            ->when($this->username, fn ($query) => $query->whereIn('user_id', User::withTrashed()->select('id')->where('username', 'LIKE', '%'.$this->username.'%')))
            ->when($this->apikey, fn ($query) => $query->where('content', 'LIKE', '%'.$this->apikey.'%'))
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    final public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.apikey-search', [
            'apikeys' => $this->apikeys,
        ]);
    }
}
