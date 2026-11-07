<article class="auth__image">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.12.2/lottie.min.js"></script>
    <div id="lottie-animation" style="width:100%;height:100%;"></div>
    <script>
        lottie.loadAnimation({
            container: document.getElementById('lottie-animation'),
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: '/images/register.json'
        });
    </script>
    @if(!$useLoginGlobal && $company->logo)
        <img class="auth__logo {{ $login->position_logo }}" src="{{ asset('storage/uploads/logos/' . $company->logo) }}" alt="Logo" />
    @endif
</article>