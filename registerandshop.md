Plan
Pending Shop Action Baada ya Registration
Summary
Customer akibofya Add to Cart, Favourite, au Rate Product bila login, action itahifadhiwa kwenye session. Baada ya registration na email verification, atarudishwa kwenye product page aliyotoka na action itaendelea. Verification ikifanywa kwenye browser/session ileile.

Implementation Changes
Tumia session structure moja auth.pending_shop_action kuhifadhi:
action type
product ID
quantity kwa cart
validated local return URL
Login na registration pages zote zipokee na kuhifadhi parameters za pending action.
Registration links za modal zitume action details kama login links zinavyofanya sasa.
Hamisha email verification route kwenda controller method inayoweza:
Verify email.
Kutekeleza pending cart/favourite action.
Kurudisha customer kwenye product page na success/error notification.
Kufungua rating modal kupitia open_rating=1.
Cart iwe idempotent: bidhaa iliyopo isiongezwe mara mbili.
Favourite iwe “add” badala ya toggle ili retry isiiondoe.
Pending action ifutwe baada ya kushughulikiwa ili verification link ikifunguliwa tena isirudie action.
Kama hakuna pending action, verification iendelee kwenda customer dashboard kama sasa.
Tumia verified middleware kwenye Add to Cart, Favourite na Rating mutations.
Customer aliye-login lakini hajaverify akibofya action, ihifadhiwe na aelekezwe verification notice.
Kubali return URLs za ndani pekee; kataa external/protocol-relative redirects.
Result kwa Kila Action
Add to Cart: bidhaa na quantity vinaongezwa, customer anarudi product page na success notification.
Favourite: bidhaa inaongezwa kwenye favourites, customer anarudi product page na notification.
Rate Product: customer anarudi product page na rating modal inafunguka ili achague stars/review.
Product unavailable: action haisitimizwi na customer anaonyeshwa ujumbe unaoeleweka.
Test Plan
Guest → cart → register → verify → product inaingia cart mara moja.
Guest → favourite → register → verify → product inaingia favourites mara moja.
Guest → rate → register → verify → product page inafunguka na rating modal.
Existing verified customer login flow inaendelea kufanya pending action kama sasa.
Unverified customer anazuiwa kufanya action hadi athibitishe email.
Invalid product, out-of-stock product, invalid quantity na unsafe redirect vinashughulikiwa salama.
Verification link ikifunguliwa mara mbili haisababishi duplicate action.
Registration ya kawaida bila pending action inaishia customer dashboard.
Assumptions
Email verification link itafunguliwa kwenye browser/session ileile.
Rating haiwezi kusubmit moja kwa moja kwa sababu stars/review hazijachaguliwa; modal ndiyo itafunguliwa.
Scope ni Add to Cart, Favourite na Rate Product pekee.
