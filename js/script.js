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

  $(document).on("change", "[name='rating']", function () {
    let val = parseInt($(this).val(), 6) || 0;
    $(this).siblings(".stars-count").attr("class", "stars-count");
    let $stars = $(this).siblings(".stars-count").find("i");
    $stars.removeClass("active");
    for (let i = 0; i < val; i++) {
      $stars.eq(i).addClass("active");
    }
  });
});
