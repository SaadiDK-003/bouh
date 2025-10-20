$(document).ready(function () {
  var owl = $(".owl-carousel").owlCarousel({
    loop: true,
    autoplay: false,
    margin: 0,
    nav: false,
    dots: false,
    rtl: true,
    items: 1,
  });

  $(".owl-next-custom-btn").click(function () {
    owl.trigger("next.owl.carousel");
  });
  $(".owl-prev-custom-btn").click(function () {
    owl.trigger("prev.owl.carousel", [300]);
  });
});
