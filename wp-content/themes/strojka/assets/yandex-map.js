document.querySelectorAll('[data-yandex-map]').forEach(container => {
  const showWidget = () => {
    container.replaceChildren();
    const frame = document.createElement('iframe');
    frame.title = 'Офис на Яндекс Картах';
    frame.src = container.dataset.widget;
    frame.loading = 'lazy';
    frame.allowFullscreen = true;
    container.append(frame);
  };
  const load = () => {
    const script = document.createElement('script');
    const url = new URL('https://api-maps.yandex.ru/2.1/');
    url.searchParams.set('apikey', container.dataset.key);
    url.searchParams.set('lang', 'ru_RU');
    script.src = url.href;
    script.async = true;
    let finished = false;
    let map;
    const fail = () => {
      if (finished) return;
      finished = true;
      clearTimeout(timeout);
      if (map) map.destroy();
      showWidget();
    };
    const timeout = setTimeout(fail, 15000);
    script.onerror = fail;
    script.onload = () => {
      if (finished) return;
      if (!window.ymaps) return fail();
      window.ymaps.ready(() => {
        if (finished) return;
        try {
          container.replaceChildren();
          map = new window.ymaps.Map(container, {center: [54.193, 37.618], zoom: 11, controls: ['zoomControl', 'fullscreenControl']});
          map.behaviors.disable('scrollZoom');
          window.ymaps.geocode(container.dataset.address, {results: 1}).then(result => {
            if (finished) return;
            const office = result.geoObjects.get(0);
            if (!office) return fail();
            office.options.set('preset', 'islands#orangeDotIcon');
            map.geoObjects.add(office);
            map.setCenter(office.geometry.getCoordinates(), 16);
            finished = true;
            clearTimeout(timeout);
          }, fail);
        } catch { fail(); }
      });
    };
    document.head.append(script);
  };
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(entries => {
      if (entries.some(entry => entry.isIntersecting)) {
        observer.disconnect();
        load();
      }
    }, {rootMargin: '250px'});
    observer.observe(container);
  } else { load(); }
});
