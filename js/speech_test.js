(function ($, Drupal, drupalSettings) {

  'use strict';

  const synth = window.speechSynthesis;
  const voiceSelect = document.querySelector("select[name=voice]");
  let voices = [];

  function populateVoiceList() {
    voices = synth.getVoices();
    let selected_voice = document.querySelector("#voice-info");

    for (const voice of voices) {
      const option = document.createElement("option");
      option.textContent = `${voice.name} (${voice.lang})`;
      option.value = voice.name;

      if (voice.name === selected_voice.textContent) {
        option.selected = true;
      }

      if (voice.default) {
        option.textContent += " — DEFAULT";
      }

      option.setAttribute("data-lang", voice.lang);
      option.setAttribute("data-name", voice.name);
      voiceSelect.appendChild(option);
    }
  }

  Drupal.behaviors.speech_test = {
    attach: function (context, settings) {
      populateVoiceList();

      $(".btn-test-speech").unbind("click").click(function(e) {
        e.preventDefault();

        const inputTxt = document.querySelector("input[name=sample_text]");
        const pitch    = document.querySelector("input[name=pitch]");
        const rate     = document.querySelector("input[name=rate]");

        if (synth.speaking) {
          // console.error("speechSynthesis.speaking");
          return;
        }

        if (inputTxt.value !== "") {
          const utterThis = new SpeechSynthesisUtterance(inputTxt.value);

          utterThis.onend = function (event) {
            // console.log("SpeechSynthesisUtterance.onend");
          };

          utterThis.onerror = function (event) {
            // console.error("SpeechSynthesisUtterance.onerror");
          };

          const selectedOption =
            voiceSelect.selectedOptions[0].getAttribute("data-name");

          for (let i = 0; i < voices.length; i++) {
            if (voices[i].name === selectedOption) {
              utterThis.voice = voices[i];
              console.log(voices[i]);
              break;
            }
          }
          utterThis.pitch = pitch.value;
          utterThis.rate = rate.value;
          synth.speak(utterThis);
        }
      });
    }
  };

}(jQuery, Drupal, drupalSettings));
