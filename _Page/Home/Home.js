 function generateRandomString(length) {
    var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    var result = "";
    for (var i = 0; i < length; i++) {
      result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
  }

  $("#ProsesGenerateRandomString").on("submit", function (e) {
    e.preventDefault();
    var count = $("#string_count").val();
    if (count < 1 || count > 36 || count === "") {
      alert("Jumlah karakter harus 1 - 36");
      return;
    }
    var randomString = generateRandomString(count);
    $("#random_string").val(randomString);
  });

  $("#CopyString").on("click", function () {
    var copyText = $("#random_string");
    copyText.select();
    document.execCommand("copy");

    $(this).html('<i class="bi bi-clipboard-check"></i>');
    setTimeout(() => {
      $(this).html('<i class="bi bi-clipboard"></i>');
    }, 1500);
  });

  // Submit form Hash Generator
$("#ProsesHashGenerator").on("submit", function (e) {
    e.preventDefault();

    var string_asli = $("#string_asli").val();

    if (string_asli === "") {
        alert("String asli tidak boleh kosong!");
        return;
    }

    $.ajax({
        type: "POST",
        url: "_Page/Home/ProsesGeneratorHash.php",
        data: { string_asli: string_asli },
        dataType: "json",
        beforeSend: function () {
            $("#string_hash").val("Memproses...");
        },
        success: function (response) {
            if (response.status === "success") {
                $("#string_hash").val(response.hash);
            } else {
                $("#string_hash").val("Gagal memproses string!");
            }
        },
        error: function () {
            $("#string_hash").val("Terjadi kesalahan server!");
        }
    });
});

// Tombol copy hasil hash
$("#CopyStringHash").on("click", function () {
    var copyText = $("#string_hash");
    copyText.select();
    document.execCommand("copy");

    $(this).html('<i class="bi bi-clipboard-check"></i>');
    setTimeout(() => {
        $(this).html('<i class="bi bi-clipboard"></i>');
    }, 1500);
});