<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">{{ __('Dashboard') }}</h2>
    </x-slot>

    @include('dashboard.hero', ['user' => $user])

    @if ($user->isCounsellor())
        @include('dashboard.counsellor', ['stats' => $stats])
    @elseif ($user->isTeacher())
        @include('dashboard.teacher', ['teacherStats' => $teacherStats])
    @elseif ($user->isAdmin())
        @include('dashboard.admin', ['adminStats' => $adminStats])
    @endif

    <script>
        // Counts each figure up from zero. The final value is already in the
        // HTML, so the numbers are right with JavaScript disabled or motion
        // reduced — a half-counted figure would be wrong data, not just wrong
        // decoration.
        (() => {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            document.querySelectorAll('.viz-count').forEach((el, index) => {
                const target = Number(el.dataset.value || 0);
                if (target === 0) return;

                const duration = 900;
                const delay = index * 80;
                let startedAt = null;
                let settled = false;
                el.textContent = '0';

                const settle = () => {
                    settled = true;
                    el.textContent = target;
                };

                const step = (now) => {
                    if (settled) return;
                    startedAt ??= now;
                    const progress = Math.min(Math.max(now - startedAt - delay, 0) / duration, 1);
                    if (progress >= 1) return settle();
                    el.textContent = Math.round(target * (1 - Math.pow(1 - progress, 3)));
                    requestAnimationFrame(step);
                };

                requestAnimationFrame(step);

                // A background tab throttles animation frames, so write the real
                // figure once regardless of how far the animation got.
                setTimeout(settle, delay + duration + 100);
            });
        })();
    </script>
</x-app-layout>
