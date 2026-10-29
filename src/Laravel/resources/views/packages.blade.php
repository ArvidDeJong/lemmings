@use('Darvis\Lemmings\Support\LemmingsConfig')
@use('Darvis\Lemmings\Support\Packages')

{{-- Plain HTML with its own small stylesheet, so the list looks the same in a host app layout and in the package's own. --}}
<x-dynamic-component :component="LemmingsConfig::layout()" title="Packages">
    <style>
        .lemmings-packages {
            max-width: 48rem;
            margin: 0 auto;
        }

        .lemmings-packages ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .lemmings-packages li {
            padding: 1rem 0;
            border-top: 1px solid #444;
        }

        .lemmings-packages li:last-child {
            border-bottom: 1px solid #444;
        }

        .lemmings-packages a {
            color: inherit;
            font-weight: 600;
        }

        .lemmings-packages code {
            margin-left: .5rem;
            font-size: .875em;
            opacity: .7;
        }

        .lemmings-packages .status {
            float: right;
            padding: .1rem .6rem;
            border-radius: 1rem;
            font-size: .8125em;
            background: #7f1d1d;
        }

        .lemmings-packages .is-installed .status {
            background: #14532d;
        }

        .lemmings-packages p {
            margin: .25rem 0 0;
        }
    </style>

    <div class="lemmings-packages">
        <h1>Packages</h1>

        <ul>
            @foreach (Packages::status() as $package)
                <li class="{{ $package['installed'] ? 'is-installed' : 'is-missing' }}">
                    <span class="status">{{ $package['version'] ?? 'not installed' }}</span>
                    <a href="{{ $package['url'] }}" target="_blank" rel="noopener">{{ $package['name'] }}</a>
                    <code>{{ $package['package'] }}</code>
                    <p>{{ $package['purpose'] }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</x-dynamic-component>
