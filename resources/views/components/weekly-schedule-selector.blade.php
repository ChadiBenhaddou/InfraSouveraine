@props(['schedule' => '{}'])

@php
    $decoded = json_decode($schedule, true);
    if (!$decoded || !is_array($decoded)) {
        $decoded = [
            'mon' => [9,10,11,12,13,14,15,16],
            'tue' => [9,10,11,12,13,14,15,16],
            'wed' => [9,10,11,12,13,14,15,16],
            'thu' => [9,10,11,12,13,14,15,16],
            'fri' => [9,10,11,12,13,14,15,16],
            'sat' => [],
            'sun' => [],
        ];
    }
    $dayMap = ['mon' => 'Lun', 'tue' => 'Mar', 'wed' => 'Mer', 'thu' => 'Jeu', 'fri' => 'Ven', 'sat' => 'Sam', 'sun' => 'Dim'];
@endphp

<div
    x-data="{
        schedule: {{ Js::from($decoded) }},
        activePreset: null,
        init() {
            this.$el.addEventListener('click', (e) => {
                const cell = e.target.closest('[data-cell]');
                if (!cell) return;
                const d = cell.dataset.day;
                const h = parseInt(cell.dataset.hour, 10);
                if (d && !isNaN(h)) this.toggle(d, h);
            });
        },
        isSel(d, h) { return Array.isArray(this.schedule[d]) && this.schedule[d].includes(h); },
        selClass(d, h) {
            if (this.isSel(d, h)) return 'bg-blue-500 border-blue-600 shadow-sm';
            if (h < 8 || h >= 20) return 'bg-gray-100 border-gray-50 hover:bg-blue-100 hover:border-blue-200';
            return 'bg-gray-50 border-gray-100 hover:bg-blue-100 hover:border-blue-200';
        },
        total() {
            let t = 0;
            for (const d of Object.values(this.schedule)) t += Array.isArray(d) ? d.length : 0;
            return t;
        },
        toggle(d, h) {
            if (this.isSel(d, h)) {
                this.schedule[d] = this.schedule[d].filter(x => x !== h);
            } else {
                if (!Array.isArray(this.schedule[d])) this.schedule[d] = [];
                this.schedule[d] = [...this.schedule[d], h].sort((a,b) => a-b);
            }
            this.activePreset = null;
            this.$wire.set('weeklySchedule', JSON.stringify(this.schedule));
        },
        setPreset(name) {
            const b = [9,10,11,12,13,14,15,16], f = [8,9,10,11,12,13,14,15,16,17,18,19], a = Array.from({length:24},(_,i)=>i), e = [];
            if (name === 'business') this.schedule = {mon:[...b],tue:[...b],wed:[...b],thu:[...b],fri:[...b],sat:[...e],sun:[...e]};
            else if (name === 'extended') this.schedule = {mon:[...f],tue:[...f],wed:[...f],thu:[...f],fri:[...f],sat:[...f],sun:[...e]};
            else if (name === '247') this.schedule = {mon:[...a],tue:[...a],wed:[...a],thu:[...a],fri:[...a],sat:[...a],sun:[...a]};
            this.activePreset = name;
            this.$wire.set('weeklySchedule', JSON.stringify(this.schedule));
        },
        clearAll() {
            this.schedule = {mon:[],tue:[],wed:[],thu:[],fri:[],sat:[],sun:[]};
            this.activePreset = null;
            this.$wire.set('weeklySchedule', JSON.stringify(this.schedule));
        },
    }"
    class="space-y-5"
>
    {{-- Presets --}}
    <div class="flex flex-wrap gap-2">
        <button type="button" @click="setPreset('business')"
            class="text-xs px-3.5 py-2 rounded-lg font-medium transition-all duration-150 border"
            :class="activePreset==='business' ? 'bg-blue-900 text-white border-blue-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-700'">
            💼 Lun-Ven 9h–17h
        </button>
        <button type="button" @click="setPreset('extended')"
            class="text-xs px-3.5 py-2 rounded-lg font-medium transition-all duration-150 border"
            :class="activePreset==='extended' ? 'bg-blue-900 text-white border-blue-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-700'">
            🚀 Lun-Sam 8h–20h
        </button>
        <button type="button" @click="setPreset('247')"
            class="text-xs px-3.5 py-2 rounded-lg font-medium transition-all duration-150 border"
            :class="activePreset==='247' ? 'bg-blue-900 text-white border-blue-900 shadow-sm' : 'bg-white text-gray-600 border-gray-200 hover:border-blue-300 hover:text-blue-700'">
            🔄 24/7
        </button>
        <button type="button" @click="clearAll()"
            class="text-xs px-3.5 py-2 rounded-lg font-medium transition-all duration-150 border bg-white text-gray-600 border-gray-200 hover:border-red-300 hover:text-red-600">
            ✕ Effacer
        </button>
    </div>

    {{-- Table --}}
    <div class="select-none overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
        <div class="p-3 min-w-[52rem]">
            <table class="w-full border-collapse" style="table-layout: fixed">
                <thead>
                    <tr>
                        <th style="width:4.5rem" class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider text-left pb-1 align-bottom"></th>
                        @foreach (range(0, 23) as $h)
                            <th style="width:2rem" class="text-[10px] font-semibold text-center pb-1 align-bottom
                                {{ $h < 8 || $h >= 20 ? 'text-gray-300' : ($h < 12 ? 'text-gray-500' : ($h < 18 ? 'text-gray-700' : 'text-gray-500')) }}">
                                {{ $h }}h
                            </th>
                        @endforeach
                        <th style="width:2.5rem" class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider text-center pb-1 align-bottom">H/j</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($dayMap as $key => $label)
                        <tr>
                            <td class="text-sm font-semibold h-8 {{ in_array($key, ['sat', 'sun']) ? 'text-gray-400' : 'text-blue-950' }}">
                                {{ $label }}
                            </td>
                            @foreach (range(0, 23) as $h)
                                <td class="p-0 align-middle">
                                    <div data-cell data-day="{{ $key }}" data-hour="{{ $h }}"
                                        class="h-8 rounded-md cursor-pointer transition-none border"
                                        :class="selClass('{{ $key }}', {{ $h }})">
                                    </div>
                                </td>
                            @endforeach
                            <td class="text-center text-xs font-bold h-8 {{ in_array($key, ['sat', 'sun']) ? 'text-gray-300' : 'text-blue-900' }}">
                                <span x-text="(schedule['{{ $key }}']||[]).length + 'h'"></span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend + Stats --}}
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm bg-white rounded-xl border border-gray-200 px-5 py-3 shadow-sm">
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded-md bg-blue-500 shadow-sm"></span>
            <span class="text-gray-500">En ligne</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded-md bg-gray-200 border border-gray-300"></span>
            <span class="text-gray-500">Hors ligne (jour)</span>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-block w-4 h-4 rounded-md bg-gray-100 border border-gray-200"></span>
            <span class="text-gray-500">Hors ligne (nuit)</span>
        </div>
        <span class="hidden sm:inline text-gray-300">|</span>
        <span class="text-gray-700 font-medium">
            <span class="text-blue-900 text-lg font-extrabold" x-text="total()"></span> h <span class="text-gray-400">/ semaine</span>
        </span>
        <span class="text-gray-300">·</span>
        <span class="text-gray-600">
            <span class="font-semibold" x-text="(total()*4.33).toFixed(0)"></span> h <span class="text-gray-400">/ mois estimées</span>
        </span>
    </div>
</div>
