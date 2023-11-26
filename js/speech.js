(function ($, Drupal, drupalSettings) {

  'use strict';

  const synth = window.speechSynthesis;
  let voices = synth.getVoices();

  // let utterance = null;
  let isPaused = null;

  Drupal.behaviors.speech_test = {
    attach: function (context, settings) {

      $(".btn-launch-speech").unbind("click").click(function(e) {
        e.preventDefault();

        if (isPaused === null) {
          var elementos = document.querySelectorAll(drupalSettings.onovas_web_speech.container);
          var textos = Array.from(elementos).map(elemento => elemento.innerText);
          console.log(textos);

          const pitch    = drupalSettings.onovas_web_speech.pitch;
          const rate     = drupalSettings.onovas_web_speech.rate;

          if (synth.speaking) {
            return;
          }

          if (textos !== "") {
            isPaused = false;
            const utterThis = new SpeechSynthesisUtterance(textos);
            const selectedOption = drupalSettings.onovas_web_speech.voice;

            for (let i = 0; i < voices.length; i++) {
              if (voices[i].name === selectedOption) {
                utterThis.voice = voices[i];
                break;
              }
            }

            utterThis.pitch = pitch;
            utterThis.rate = rate;
            synth.speak(utterThis);

            $(".btn-launch-speech").text(Drupal.t("Pause"));
          }
        }
        else if (synth.speaking && isPaused === false) {
          synth.pause();
          isPaused = true;
          $(".btn-launch-speech").text(Drupal.t("Play"));
        }
        else if (isPaused) {
          synth.resume();
          isPaused = false;
          $(".btn-launch-speech").text(Drupal.t("Pause"));
        }
      });

      $(".btn-stop-speech").unbind("click").click(function(e) {
        e.preventDefault();
        isPaused = null;
        window.speechSynthesis.cancel();
        $(".btn-launch-speech").text(Drupal.t("Play"));
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
