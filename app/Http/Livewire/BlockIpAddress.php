<?php
/**
 * NOTICE OF LICENSE.
 *
 * UNIT3D Community Edition is open-sourced software licensed under the GNU Affero General Public License v3.0
 * The details is bundled with this project in the file LICENSE.txt.
 *
 * @project    UNIT3D Community Edition
 *
 * @author     HDVinnie <hdinnovations@protonmail.com>
 * @license    https://www.gnu.org/licenses/agpl-3.0.en.html/ GNU Affero General Public License v3.0
 */

namespace App\Http\Livewire;

use App\Models\BlockedIp;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BlockIpAddress extends Component
{
    use WithPagination;

    #[Url]
    public string $ipAddress = '';

    #[Url]
    public string $reason = '';

    #[Url]
    public int $perPage = 25;

    protected array $rules = [
        'reason' => [
            'required',
            'filled',
        ],
    ];

    final public function updatedPage(): void
    {
        $this->dispatch('paginationChanged');
    }

    final public function store(): void
    {
        abort_unless(auth()->user()->group->is_modo, 403);

        $this->validate();

        BlockedIp::create([
            'ip_address' => $this->ipAddress,
            'reason'     => $this->reason,
            'user_id'    => auth()->user()->id,
        ]);

        $this->reason = '';

        cache()->forget('blocked-ips');

        $this->dispatch('success', type: 'success', message: 'Ip Addresses Successfully Blocked!');
    }

    final public function destroy(BlockedIp $blockedIp): void
    {
        if (auth()->user()->group->is_modo) {
            $blockedIp->delete();

            $this->dispatch('success', type: 'success', message: 'IP has successfully been deleted!');
        } else {
            $this->dispatch('error', type:  'error', message: 'Permission Denied!');
        }
    }

    #[Computed]
    final public function ipAddresses(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return BlockedIp::query()
            ->latest()
            ->paginate($this->perPage);
    }

    final public function render(): \Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application
    {
        return view('livewire.block-ip-address', [
            'ipAddresses' => $this->ipAddresses,
        ]);
    }
}
