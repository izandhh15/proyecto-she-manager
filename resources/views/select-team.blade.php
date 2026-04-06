<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 pb-8">
        <div class="mt-6 mb-6">
            <h2 class="font-heading text-2xl lg:text-3xl font-bold uppercase tracking-wide text-text-primary">{{ __('app.new_game') }}</h2>
            <p class="mt-2 max-w-3xl text-sm md:text-base text-text-secondary">
                {{ __('game.new_game_flow_help') }}
            </p>
        </div>

        @php
            $allCompetitions = collect($countries)->flatMap(fn ($c) => collect($c['tiers']))->values();
            $firstId = $allCompetitions->first()?->id;
        @endphp

        <div x-data="{
                managementMode: 'club_only',
                openTab: '{{ $firstId }}',
                loading: false,
            }">
            <form method="post" action="{{ route('init-game') }}" @submit="loading = true" class="space-y-6">
                @csrf

                <x-input-error :messages="$errors->get('club_team_id')" class="mt-2"/>
                <x-input-error :messages="$errors->get('national_team_id')" class="mt-2"/>

                <input type="hidden" name="management_mode" :value="managementMode">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4">
                    <button type="button"
                            @click="managementMode = 'club_only'"
                            :class="managementMode === 'club_only' ? 'ring-2 ring-accent-primary border-accent-primary/30 bg-accent-primary/5' : 'border-border-strong hover:bg-surface-700/50'"
                            class="relative flex items-center gap-4 p-4 rounded-xl border transition-all duration-200 text-left">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-base uppercase tracking-wide text-text-body">{{ __('game.mode_club_only') }}</h3>
                            <p class="text-xs mt-0.5 text-text-muted">{{ __('game.mode_club_only_desc') }}</p>
                        </div>
                    </button>

                    <button type="button"
                            @click="managementMode = 'national_only'"
                            :class="managementMode === 'national_only' ? 'ring-2 ring-accent-gold border-accent-gold/30 bg-accent-gold/5' : 'border-border-strong hover:bg-surface-700/50'"
                            class="relative flex items-center gap-4 p-4 rounded-xl border transition-all duration-200 text-left">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-base uppercase tracking-wide text-text-body">{{ __('game.mode_national_only') }}</h3>
                            <p class="text-xs mt-0.5 text-text-muted">{{ __('game.mode_national_only_desc') }}</p>
                        </div>
                    </button>

                    <button type="button"
                            @click="managementMode = 'club_national'"
                            :class="managementMode === 'club_national' ? 'ring-2 ring-accent-blue border-accent-blue/30 bg-accent-blue/5' : 'border-border-strong hover:bg-surface-700/50'"
                            class="relative flex items-center gap-4 p-4 rounded-xl border transition-all duration-200 text-left">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-heading font-bold text-base uppercase tracking-wide text-text-body">{{ __('game.mode_club_and_national') }}</h3>
                            <p class="text-xs mt-0.5 text-text-muted">{{ __('game.mode_club_and_national_desc') }}</p>
                        </div>
                    </button>
                </div>

                <div x-show="managementMode === 'club_only' || managementMode === 'club_national'" x-cloak class="space-y-4">
                    <div class="flex gap-2 overflow-x-auto scrollbar-hide">
                        @foreach($countries as $countryCode => $country)
                            @foreach($country['tiers'] as $tier => $competition)
                                <x-pill-button
                                    @click="openTab = '{{ $competition->id }}'"
                                    x-bind:class="openTab === '{{ $competition->id }}' ? 'bg-accent-primary text-white' : 'bg-surface-700 text-text-secondary hover:text-text-body hover:bg-surface-600'"
                                    class="gap-2 shrink-0">
                                    <x-competition-logo :competition="$competition" class="h-4 w-auto max-w-12 shrink-0" />
                                    <span>{{ __($competition->name) }}</span>
                                </x-pill-button>
                            @endforeach
                        @endforeach
                    </div>

                    @foreach($countries as $countryCode => $country)
                        @foreach($country['tiers'] as $tier => $competition)
                            <div x-show="openTab === '{{ $competition->id }}'" x-cloak>
                                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
                                    @foreach($competition->teams as $team)
                                        <label class="flex items-start gap-3 rounded-xl border border-border-default p-4 cursor-pointer transition-all hover:bg-accent-blue/5 hover:border-accent-blue/30 has-checked:ring-2 has-checked:ring-accent-blue has-checked:border-accent-blue/30 has-checked:bg-accent-blue/5">
                                            <x-team-crest :team="$team" class="w-11 h-11 shrink-0 mt-0.5" />
                                            <div class="min-w-0 flex-1">
                                                <p class="text-sm md:text-base font-semibold text-text-body truncate">{{ $team->name }}</p>
                                                <p class="text-xs uppercase tracking-wide text-text-muted">{{ $country['name'] }}</p>
                                            </div>
                                            <input x-bind:required="managementMode === 'club_only' || managementMode === 'club_national'" x-bind:disabled="managementMode === 'national_only'" type="radio" name="club_team_id" value="{{ $team->id }}" class="hidden">
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    @endforeach
                </div>

                <div x-show="managementMode === 'national_only' || managementMode === 'club_national'" x-cloak class="space-y-4">
                    <div class="rounded-xl border border-border-default bg-surface-800/80 p-4">
                        <label for="national_team_id" class="block text-sm font-semibold text-text-body mb-2">{{ __('game.select_national_team') }}</label>
                        <select id="national_team_id" name="national_team_id" x-bind:required="managementMode === 'national_only' || managementMode === 'club_national'" class="w-full rounded-lg border-border-default bg-surface-700 text-text-body">
                            <option value="">{{ __('game.select_national_team_placeholder') }}</option>
                            @foreach($nationalTeams as $nationalTeam)
                                <option value="{{ $nationalTeam->id }}">{{ $nationalTeam->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-text-muted">{{ __('game.national_team_only_senior_help') }}</p>
                    </div>
                </div>

                <div class="flex justify-center pt-2">
                    <x-primary-button-spin>
                        {{ __('game.start_game') }}
                    </x-primary-button-spin>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
