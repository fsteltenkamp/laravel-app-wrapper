<section class="w-full">
    @include('partials.settings-heading')

    <x-fltc::heading level="2" class="sr-only">{{ __('Appearance settings') }}</x-fltc::heading>

    <x-settings.layout :heading="__('Appearance')" :subheading="__('Update the appearance settings for your account')">

        {{-- Dark mode toggle --}}
        <div class="flex items-center gap-4">
            <x-fltc::darkmode.toggle :theme="$mainTheme" />
            <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ __('Toggle light and dark mode') }}</span>
        </div>

        <hr class="my-6 border-zinc-200 dark:border-zinc-700">

        {{-- Preset selector --}}
        <div class="mb-6">
            <x-fltc::form.input.select id="preset" label="Vordefiniertes Design" model="preset" :live="true" theme="{{ $accentTheme }}" placeholder="— Benutzerdefiniert —">
                @foreach ($presets as $key => $p)
                    <option value="{{ $key }}" @selected($preset === $key)>{{ $p['label'] }}</option>
                @endforeach
            </x-fltc::form.input.select>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ein Preset auswählen überschreibt alle individuellen Farbeinstellungen automatisch.</p>
        </div>

        {{-- Individual colour selects in 2-column grid --}}
        <div class="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-2">

            <div>
                <x-fltc::form.input.select id="mainTheme" label="Hauptfarbe" model="mainTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($mainTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Grundfarbe der Benutzeroberfläche – Navigation, Karten, Seitenlayout und Überschriften.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="secondaryTheme" label="Sekundärfarbe" model="secondaryTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($secondaryTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Ergänzende Farbe für Hintergründe, Trennlinien und sekundäre Elemente.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="accentTheme" label="Akzentfarbe" model="accentTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($accentTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Hebt interaktive Elemente hervor – Formularfelder, Links und aktive Zustände.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="infoTheme" label="Info-Farbe" model="infoTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($infoTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Wird für Informationsmeldungen, Hinweise und neutrale Statusanzeigen verwendet.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="successTheme" label="Erfolgsfarbe" model="successTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($successTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kennzeichnet erfolgreiche Aktionen, Bestätigungen und positive Rückmeldungen.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="warningTheme" label="Warnfarbe" model="warningTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($warningTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Zeigt Warnhinweise und Aktionen an, die besondere Aufmerksamkeit erfordern.</p>
            </div>

            <div>
                <x-fltc::form.input.select id="errorTheme" label="Fehlerfarbe" model="errorTheme" :live="true" theme="{{ $accentTheme }}">
                    @foreach ($availableThemes as $t)
                        <option value="{{ $t }}" @selected($errorTheme === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </x-fltc::form.input.select>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Markiert Fehler, Validierungsprobleme und kritische Systemhinweise.</p>
            </div>

        </div>

        {{-- Color palette reference --}}
        <div class="mt-8">
            <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Farbpalette</h3>

            {{-- Shade header --}}
            <div class="mb-1 flex items-center gap-1">
                <div class="w-20 shrink-0"></div>
                @foreach (['50','100','200','300','400','500','600','700','800','900','950'] as $shade)
                    <div class="flex-1 text-center text-xs text-gray-400 dark:text-gray-500">{{ $shade }}</div>
                @endforeach
            </div>

            {{-- Rows --}}
            @foreach ([
                'red','orange','amber','yellow','lime','green','emerald','teal',
                'cyan','sky','blue','indigo','violet','purple','fuchsia','pink','rose',
                'slate','gray','zinc','neutral','stone','taupe','mauve','mist','olive'
            ] as $color)
            <div class="mb-1 flex items-center gap-1">
                <div class="w-20 shrink-0 text-xs text-gray-600 dark:text-gray-400">{{ ucfirst($color) }}</div>
                @php $shades = ['50','100','200','300','400','500','600','700','800','900','950']; @endphp
                @foreach ($shades as $i => $shade)
                    <div class="h-7 flex-1 {{ $i === 0 ? 'rounded-l' : ($i === count($shades)-1 ? 'rounded-r' : '') }}
                        @switch($color)
                            @case('red')       @switch($shade) @case('50') bg-red-50 @break @case('100') bg-red-100 @break @case('200') bg-red-200 @break @case('300') bg-red-300 @break @case('400') bg-red-400 @break @case('500') bg-red-500 @break @case('600') bg-red-600 @break @case('700') bg-red-700 @break @case('800') bg-red-800 @break @case('900') bg-red-900 @break @case('950') bg-red-950 @break @endswitch @break
                            @case('orange')    @switch($shade) @case('50') bg-orange-50 @break @case('100') bg-orange-100 @break @case('200') bg-orange-200 @break @case('300') bg-orange-300 @break @case('400') bg-orange-400 @break @case('500') bg-orange-500 @break @case('600') bg-orange-600 @break @case('700') bg-orange-700 @break @case('800') bg-orange-800 @break @case('900') bg-orange-900 @break @case('950') bg-orange-950 @break @endswitch @break
                            @case('amber')     @switch($shade) @case('50') bg-amber-50 @break @case('100') bg-amber-100 @break @case('200') bg-amber-200 @break @case('300') bg-amber-300 @break @case('400') bg-amber-400 @break @case('500') bg-amber-500 @break @case('600') bg-amber-600 @break @case('700') bg-amber-700 @break @case('800') bg-amber-800 @break @case('900') bg-amber-900 @break @case('950') bg-amber-950 @break @endswitch @break
                            @case('yellow')    @switch($shade) @case('50') bg-yellow-50 @break @case('100') bg-yellow-100 @break @case('200') bg-yellow-200 @break @case('300') bg-yellow-300 @break @case('400') bg-yellow-400 @break @case('500') bg-yellow-500 @break @case('600') bg-yellow-600 @break @case('700') bg-yellow-700 @break @case('800') bg-yellow-800 @break @case('900') bg-yellow-900 @break @case('950') bg-yellow-950 @break @endswitch @break
                            @case('lime')      @switch($shade) @case('50') bg-lime-50 @break @case('100') bg-lime-100 @break @case('200') bg-lime-200 @break @case('300') bg-lime-300 @break @case('400') bg-lime-400 @break @case('500') bg-lime-500 @break @case('600') bg-lime-600 @break @case('700') bg-lime-700 @break @case('800') bg-lime-800 @break @case('900') bg-lime-900 @break @case('950') bg-lime-950 @break @endswitch @break
                            @case('green')     @switch($shade) @case('50') bg-green-50 @break @case('100') bg-green-100 @break @case('200') bg-green-200 @break @case('300') bg-green-300 @break @case('400') bg-green-400 @break @case('500') bg-green-500 @break @case('600') bg-green-600 @break @case('700') bg-green-700 @break @case('800') bg-green-800 @break @case('900') bg-green-900 @break @case('950') bg-green-950 @break @endswitch @break
                            @case('emerald')   @switch($shade) @case('50') bg-emerald-50 @break @case('100') bg-emerald-100 @break @case('200') bg-emerald-200 @break @case('300') bg-emerald-300 @break @case('400') bg-emerald-400 @break @case('500') bg-emerald-500 @break @case('600') bg-emerald-600 @break @case('700') bg-emerald-700 @break @case('800') bg-emerald-800 @break @case('900') bg-emerald-900 @break @case('950') bg-emerald-950 @break @endswitch @break
                            @case('teal')      @switch($shade) @case('50') bg-teal-50 @break @case('100') bg-teal-100 @break @case('200') bg-teal-200 @break @case('300') bg-teal-300 @break @case('400') bg-teal-400 @break @case('500') bg-teal-500 @break @case('600') bg-teal-600 @break @case('700') bg-teal-700 @break @case('800') bg-teal-800 @break @case('900') bg-teal-900 @break @case('950') bg-teal-950 @break @endswitch @break
                            @case('cyan')      @switch($shade) @case('50') bg-cyan-50 @break @case('100') bg-cyan-100 @break @case('200') bg-cyan-200 @break @case('300') bg-cyan-300 @break @case('400') bg-cyan-400 @break @case('500') bg-cyan-500 @break @case('600') bg-cyan-600 @break @case('700') bg-cyan-700 @break @case('800') bg-cyan-800 @break @case('900') bg-cyan-900 @break @case('950') bg-cyan-950 @break @endswitch @break
                            @case('sky')       @switch($shade) @case('50') bg-sky-50 @break @case('100') bg-sky-100 @break @case('200') bg-sky-200 @break @case('300') bg-sky-300 @break @case('400') bg-sky-400 @break @case('500') bg-sky-500 @break @case('600') bg-sky-600 @break @case('700') bg-sky-700 @break @case('800') bg-sky-800 @break @case('900') bg-sky-900 @break @case('950') bg-sky-950 @break @endswitch @break
                            @case('blue')      @switch($shade) @case('50') bg-blue-50 @break @case('100') bg-blue-100 @break @case('200') bg-blue-200 @break @case('300') bg-blue-300 @break @case('400') bg-blue-400 @break @case('500') bg-blue-500 @break @case('600') bg-blue-600 @break @case('700') bg-blue-700 @break @case('800') bg-blue-800 @break @case('900') bg-blue-900 @break @case('950') bg-blue-950 @break @endswitch @break
                            @case('indigo')    @switch($shade) @case('50') bg-indigo-50 @break @case('100') bg-indigo-100 @break @case('200') bg-indigo-200 @break @case('300') bg-indigo-300 @break @case('400') bg-indigo-400 @break @case('500') bg-indigo-500 @break @case('600') bg-indigo-600 @break @case('700') bg-indigo-700 @break @case('800') bg-indigo-800 @break @case('900') bg-indigo-900 @break @case('950') bg-indigo-950 @break @endswitch @break
                            @case('violet')    @switch($shade) @case('50') bg-violet-50 @break @case('100') bg-violet-100 @break @case('200') bg-violet-200 @break @case('300') bg-violet-300 @break @case('400') bg-violet-400 @break @case('500') bg-violet-500 @break @case('600') bg-violet-600 @break @case('700') bg-violet-700 @break @case('800') bg-violet-800 @break @case('900') bg-violet-900 @break @case('950') bg-violet-950 @break @endswitch @break
                            @case('purple')    @switch($shade) @case('50') bg-purple-50 @break @case('100') bg-purple-100 @break @case('200') bg-purple-200 @break @case('300') bg-purple-300 @break @case('400') bg-purple-400 @break @case('500') bg-purple-500 @break @case('600') bg-purple-600 @break @case('700') bg-purple-700 @break @case('800') bg-purple-800 @break @case('900') bg-purple-900 @break @case('950') bg-purple-950 @break @endswitch @break
                            @case('fuchsia')   @switch($shade) @case('50') bg-fuchsia-50 @break @case('100') bg-fuchsia-100 @break @case('200') bg-fuchsia-200 @break @case('300') bg-fuchsia-300 @break @case('400') bg-fuchsia-400 @break @case('500') bg-fuchsia-500 @break @case('600') bg-fuchsia-600 @break @case('700') bg-fuchsia-700 @break @case('800') bg-fuchsia-800 @break @case('900') bg-fuchsia-900 @break @case('950') bg-fuchsia-950 @break @endswitch @break
                            @case('pink')      @switch($shade) @case('50') bg-pink-50 @break @case('100') bg-pink-100 @break @case('200') bg-pink-200 @break @case('300') bg-pink-300 @break @case('400') bg-pink-400 @break @case('500') bg-pink-500 @break @case('600') bg-pink-600 @break @case('700') bg-pink-700 @break @case('800') bg-pink-800 @break @case('900') bg-pink-900 @break @case('950') bg-pink-950 @break @endswitch @break
                            @case('rose')      @switch($shade) @case('50') bg-rose-50 @break @case('100') bg-rose-100 @break @case('200') bg-rose-200 @break @case('300') bg-rose-300 @break @case('400') bg-rose-400 @break @case('500') bg-rose-500 @break @case('600') bg-rose-600 @break @case('700') bg-rose-700 @break @case('800') bg-rose-800 @break @case('900') bg-rose-900 @break @case('950') bg-rose-950 @break @endswitch @break
                            @case('slate')     @switch($shade) @case('50') bg-slate-50 @break @case('100') bg-slate-100 @break @case('200') bg-slate-200 @break @case('300') bg-slate-300 @break @case('400') bg-slate-400 @break @case('500') bg-slate-500 @break @case('600') bg-slate-600 @break @case('700') bg-slate-700 @break @case('800') bg-slate-800 @break @case('900') bg-slate-900 @break @case('950') bg-slate-950 @break @endswitch @break
                            @case('gray')      @switch($shade) @case('50') bg-gray-50 @break @case('100') bg-gray-100 @break @case('200') bg-gray-200 @break @case('300') bg-gray-300 @break @case('400') bg-gray-400 @break @case('500') bg-gray-500 @break @case('600') bg-gray-600 @break @case('700') bg-gray-700 @break @case('800') bg-gray-800 @break @case('900') bg-gray-900 @break @case('950') bg-gray-950 @break @endswitch @break
                            @case('zinc')      @switch($shade) @case('50') bg-zinc-50 @break @case('100') bg-zinc-100 @break @case('200') bg-zinc-200 @break @case('300') bg-zinc-300 @break @case('400') bg-zinc-400 @break @case('500') bg-zinc-500 @break @case('600') bg-zinc-600 @break @case('700') bg-zinc-700 @break @case('800') bg-zinc-800 @break @case('900') bg-zinc-900 @break @case('950') bg-zinc-950 @break @endswitch @break
                            @case('neutral')   @switch($shade) @case('50') bg-neutral-50 @break @case('100') bg-neutral-100 @break @case('200') bg-neutral-200 @break @case('300') bg-neutral-300 @break @case('400') bg-neutral-400 @break @case('500') bg-neutral-500 @break @case('600') bg-neutral-600 @break @case('700') bg-neutral-700 @break @case('800') bg-neutral-800 @break @case('900') bg-neutral-900 @break @case('950') bg-neutral-950 @break @endswitch @break
                            @case('stone')     @switch($shade) @case('50') bg-stone-50 @break @case('100') bg-stone-100 @break @case('200') bg-stone-200 @break @case('300') bg-stone-300 @break @case('400') bg-stone-400 @break @case('500') bg-stone-500 @break @case('600') bg-stone-600 @break @case('700') bg-stone-700 @break @case('800') bg-stone-800 @break @case('900') bg-stone-900 @break @case('950') bg-stone-950 @break @endswitch @break
                            @case('taupe')     @switch($shade) @case('50') bg-taupe-50 @break @case('100') bg-taupe-100 @break @case('200') bg-taupe-200 @break @case('300') bg-taupe-300 @break @case('400') bg-taupe-400 @break @case('500') bg-taupe-500 @break @case('600') bg-taupe-600 @break @case('700') bg-taupe-700 @break @case('800') bg-taupe-800 @break @case('900') bg-taupe-900 @break @case('950') bg-taupe-950 @break @endswitch @break
                            @case('mauve')     @switch($shade) @case('50') bg-mauve-50 @break @case('100') bg-mauve-100 @break @case('200') bg-mauve-200 @break @case('300') bg-mauve-300 @break @case('400') bg-mauve-400 @break @case('500') bg-mauve-500 @break @case('600') bg-mauve-600 @break @case('700') bg-mauve-700 @break @case('800') bg-mauve-800 @break @case('900') bg-mauve-900 @break @case('950') bg-mauve-950 @break @endswitch @break
                            @case('mist')      @switch($shade) @case('50') bg-mist-50 @break @case('100') bg-mist-100 @break @case('200') bg-mist-200 @break @case('300') bg-mist-300 @break @case('400') bg-mist-400 @break @case('500') bg-mist-500 @break @case('600') bg-mist-600 @break @case('700') bg-mist-700 @break @case('800') bg-mist-800 @break @case('900') bg-mist-900 @break @case('950') bg-mist-950 @break @endswitch @break
                            @case('olive')     @switch($shade) @case('50') bg-olive-50 @break @case('100') bg-olive-100 @break @case('200') bg-olive-200 @break @case('300') bg-olive-300 @break @case('400') bg-olive-400 @break @case('500') bg-olive-500 @break @case('600') bg-olive-600 @break @case('700') bg-olive-700 @break @case('800') bg-olive-800 @break @case('900') bg-olive-900 @break @case('950') bg-olive-950 @break @endswitch @break
                        @endswitch
                    "></div>
                @endforeach
            </div>
            @endforeach
        </div>

    </x-settings.layout>
</section>
