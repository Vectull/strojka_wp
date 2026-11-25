# SEO: что подготовлено и что проверить при запуске

Техническая основа реализована в `wp-content/themes/strojka/inc/seo.php`.

- Уникальные Title и Description главной, услуг и вакансий. Поля ACF на страницах и услугах; главная — «Настроить → SEO и поисковые системы».
- Чистые адреса: `/vakansii/`, `/uslugi/stroitelstvo-domov/`, `/uslugi/fundamenty-i-zemlyanye-raboty/`, `/uslugi/blagoustroystvo-uchastka/`.
- Один canonical без параметров формы, Open Graph и карточки соцсетей.
- JSON-LD: GeneralContractor, WebSite, WebPage, BreadcrumbList и Service. Нет вымышленных оценок и отзывов.
- Стандартная карта WordPress `/wp-sitemap.xml`: страницы и услуги; без авторов, таксономий и страниц с `seo_noindex`.
- Локальная индексация закрыта. Sitemap автоматически включается при открытии индексации в настройках WordPress.
- Архивы, поиск, вложения, ошибки 404 и страницы результатов отправки формы помечаются noindex.
- Служебные страницы «Hello world!» и «Sample Page» переведены в черновики, если их исходные названия не менялись.
- При активации Yoast/Rank Math/AIOSEO/SEOPress встроенный вывод SEO отключается, чтобы не дублировать метатеги. После установки другого SEO-плагина перенесите значения полей в его настройки.

На хостинге:

1. Привязать рабочий домен, включить HTTPS, заменить siteurl/home, ссылки в базе через WP-CLI search-replace со `--skip-columns=guid` и сначала `--dry-run`. Не копировать локальный wp-config.php.
2. Проверить фактические адрес, телефон, реквизиты, численные показатели и условия работ. Убрать демонстрационные данные и фотографии, если они не подходят.
3. Настроить постоянные ссылки. Для Apache приложен `.htaccess`; для Nginx нужен `try_files $uri $uri/ /index.php?$args` и запрет доступа к служебным файлам/папкам.
4. Проверить канонические адреса, редиректы HTTP/HTTPS и WWW, коды 200/301/404, отсутствие ссылок на localhost. Одна страница — один H1.
5. Добавить собственный контент услуг: реальные проекты, фотографии, условия и ответы на вопросы. Текущие страницы — основа, а не завершённая стратегия продвижения.
6. Проверить скорость и Core Web Vitals на хостинге через PageSpeed Insights. Настроить кеширование и сжатие с учётом хостинга; локальные измерения не заменяют проверку опубликованного сайта.
7. Проверить разметку в Rich Results Test/Schema Validator, особенно адрес компании. Микроразметка не гарантирует расширенный результат в поиске.
8. Снять запрет индексации: «Настройки → Чтение». Проверить `/robots.txt` и `/wp-sitemap.xml`, доступность всех XML-карт и отсутствие noindex у публичных страниц.
9. Добавить домен в Яндекс Вебмастер и Google Search Console, внести коды подтверждения в настройки темы и отправить `/wp-sitemap.xml`.
10. Внести реальные ссылки Telegram и MAX в «Настроить → Контакты компании». До этого значки отключены.

Ссылки: [WordPress sitemap](https://developer.wordpress.org/reference/classes/wp_sitemaps/), [Google: sitemap](https://developers.google.com/search/docs/crawling-indexing/sitemaps/build-sitemap), [Google: LocalBusiness](https://developers.google.com/search/docs/appearance/structured-data/local-business).
