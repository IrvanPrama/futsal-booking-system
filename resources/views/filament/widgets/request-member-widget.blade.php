<x-filament::widget>
    <x-filament::card>
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold">Request Membership</h2>

            <x-filament::button
                color="success"
                tag="a"
                wire:click="goToCreateMember"
                target="_blank"
            >
                Kirim WhatsApp
            </x-filament::button>
        </div>
    </x-filament::card>
</x-filament::widget>
<!-- href="https://wa.me/6285117535972?text=Halo%20Admin,%20saya%20ingin%20request%20membership." -->

