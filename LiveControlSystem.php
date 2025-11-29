<?php 
require "config.php"; 

$wsUrl = getenv('Ec2Websocket') ?: 'wss://world.tsunamiflow.club';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Mishuba Live Broadcaster Console</title>
<style>
    body {
        font-family: system-ui, sans-serif;
        background: #0b0b0b;
        color: #eee;
        text-align: center;
        padding: 20px;
    }
    video, audio {
        margin: 10px;
        border-radius: 10px;
        background: #000;
    }
    input, button, select {
        margin: 5px;
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        outline: none;
    }
    button {
        background: #1a1a1a;
        color: #fff;
        cursor: pointer;
        transition: 0.2s;
    }
    button:hover { background: #333; }
    .section { margin-top: 20px; }
    .soundboard button {
        display: inline-block;
        margin: 6px;
        padding: 10px 14px;
        border-radius: 10px;
        border: 1px solid #333;
    }
    .soundboard button:hover { background: #333; }
    .sliders { display: flex; justify-content: center; flex-wrap: wrap; margin-top: 10px; }
    .slider-group { margin: 10px; text-align: center; }
    input[type=range] {
        width: 150px;
        cursor: pointer;
    }
</style>
</head>
<body>
<h1>🌊 Mishuba Live Broadcaster Console</h1>

<video id="preview" autoplay muted playsinline style="width:400px;height:auto;"></video><br>

<div>
    <input id="streamKey" placeholder="Enter Stream Key" />
    <button id="start">Start Broadcast</button>
    <button id="stop" disabled>Stop</button><br>
    <label><input type="checkbox" id="videoToggle" checked> Include Video</label>
    <label><input type="checkbox" id="musicToggle" checked> Include Music</label>
</div>

<div class="section playlist">
    <h3>🎶 Music Player</h3>
    <audio id="music" controls></audio><br>
    <select id="playlist">
        <option value="">-- Select Song --</option>
        <option value="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3">Song 1</option>
        <option value="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3">Song 2</option>
        <option value="https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3">Song 3</option>
    </select>
    <input type="file" id="fileInput" accept="audio/*">
</div>

<div class="section soundboard">
    <h3>🎛️ Sound Effects</h3>
    <button data-sound="crowd">Crowd Clapping</button>
    <button data-sound="bomb">Bomb</button>
    <button data-sound="gun">Gun Shots</button>
    <button data-sound="laugh">Crowd Laughing</button>
    <button data-sound="intro">Intro</button>
    <button data-sound="hellnah">Hell Nah</button>
    <button data-sound="shock">Shocked</button>
    <button data-sound="wtf">Questioned (WTF)</button>
    <button data-sound="other">Other</button>
</div>

<div class="section sliders">
    <div class="slider-group">
        <label>🎤 Mic Volume</label><br>
        <input type="range" id="micVol" min="0" max="1" step="0.01" value="1">
    </div>
    <div class="slider-group">
        <label>🎵 Music Volume</label><br>
        <input type="range" id="musicVol" min="0" max="1" step="0.01" value="0.8">
    </div>
    <div class="slider-group">
        <label>💥 Effects Volume</label><br>
        <input type="range" id="fxVol" min="0" max="1" step="0.01" value="0.9">
    </div>
</div>

<script>
const startBtn = document.getElementById("start");
const stopBtn = document.getElementById("stop");
const preview = document.getElementById("preview");
const videoToggle = document.getElementById("videoToggle");
const musicToggle = document.getElementById("musicToggle");
const music = document.getElementById("music");
const playlist = document.getElementById("playlist");
const fileInput = document.getElementById("fileInput");
const soundButtons = document.querySelectorAll(".soundboard button");

let ws, recorder, finalStream, audioCtx, mixedStream;
let micGain, musicGain, fxGain;

// 🎧 Preload sound effects
const sounds = {
    crowd: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/Applause Crowd Cheering sound effect.mp3"),
    bomb: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/The sound of a bomb blast Sound Effect   ((HD)).mp3"),
    gun: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/Mossberg 590 a1 Shotgun Sound Effect (Loading and shooting) (3_10 Guns).mp3"),
    laugh: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/Big Crowd Laughing Sound Effect.mp3"),
    intro: new Audio("https://actions.google.com/sounds/v1/cartoon/cartoon_cowbell.ogg"),
    hellnah: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/Oh my god, Oh hell nah - Meme Sound Effect.mp3"),
    shock: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/I cant believe youve done this (Full Facepunch Meme) - Sound Effect for editing.mp3"),
    wtf: new Audio("https://radio.tsunamiflow.club/Sound Effects/Live/The sound of a bomb blast Sound Effect   ((HD)).mp3"),
    other: new Audio("https://actions.google.com/sounds/v1/cartoon/clang_and_wobble.ogg")
};

// 📂 Playlist + Upload
playlist.onchange = () => {
    if (playlist.value) {
        music.src = playlist.value;
        music.play();
    }
};
fileInput.onchange = (e) => {
    const file = e.target.files[0];
    if (file) {
        music.src = URL.createObjectURL(file);
        music.play();
    }
};

// 🎚 Volume Controls
document.getElementById("micVol").oninput = e => micGain && (micGain.gain.value = e.target.value);
document.getElementById("musicVol").oninput = e => musicGain && (musicGain.gain.value = e.target.value);
document.getElementById("fxVol").oninput = e => fxGain && (fxGain.gain.value = e.target.value);

// 🔊 Soundboard
soundButtons.forEach(btn => {
    btn.onclick = () => playEffect(btn.dataset.sound);
});

function playEffect(name) {
    const s = sounds[name];
    if (!s) return;
    s.currentTime = 0;
    s.play().catch(err => console.error(err));
}


// Pre-create sources
const audioCtx = new AudioContext();
const musicSource = audioCtx.createMediaElementSource(music);
const fxSources = {};
for (const key in sounds) {
    fxSources[key] = audioCtx.createMediaElementSource(sounds[key]);
}

// 🧠 Create Mixed Stream
async function createMixedStream() {
    const destination = audioCtx.createMediaStreamDestination();

    // Mic
    const micStream = await navigator.mediaDevices.getUserMedia({ audio: true });
    const micSource = audioCtx.createMediaStreamSource(micStream);
    micGain = audioCtx.createGain();
    micGain.gain.value = document.getElementById("micVol").value;
    micSource.connect(micGain).connect(destination);

    // Music
    if (musicToggle.checked) {
        musicGain = audioCtx.createGain();
        musicGain.gain.value = document.getElementById("musicVol").value;
        musicSource.connect(musicGain).connect(destination);
        musicSource.connect(audioCtx.destination);
    }

    // FX
    fxGain = audioCtx.createGain();
    fxGain.gain.value = document.getElementById("fxVol").value;
    for (const key in fxSources) {
    fxSources[key].connect(fxGain).connect(destination);
    fxSources[key].connect(audioCtx.destination); // optional, if you want local playback
}

    mixedStream = destination.stream;
    return mixedStream;
}

// 🚀 Start Broadcast
async function startBroadcast() {
    const key = document.getElementById("streamKey").value.trim();
    if (!key) return alert("Enter stream key");

    startBtn.disabled = true;

    try {
        const mixed = await createMixedStream();

        let finalTracks = [];
        if (videoToggle.checked) {
            const camStream = await navigator.mediaDevices.getUserMedia({ video: true });
            preview.srcObject = camStream;
            finalTracks = [...camStream.getVideoTracks(), ...mixed.getAudioTracks()];
        } else {
            preview.srcObject = mixed;
            finalTracks = [...mixed.getAudioTracks()];
        }

        finalStream = new MediaStream(finalTracks);
        

ws = new WebSocket("<?= $wsUrl ?>?key=" + encodeURIComponent(key));

        ws.binaryType = "arraybuffer";

        ws.onopen = () => {
           const mime = MediaRecorder.isTypeSupported("video/webm;codecs=vp8,opus") 
    ? "video/webm;codecs=vp8,opus" 
    : "video/webm";
            recorder = new MediaRecorder(finalStream, { mimeType: mime });

            recorder.ondataavailable = async (e) => {
                if (e.data.size > 0 && ws.readyState === WebSocket.OPEN)
                    ws.send(await e.data.arrayBuffer());
            };

            recorder.onstart = () => {
                stopBtn.disabled = false;
                console.log("🎥 Streaming started");
            };

            recorder.start(videoToggle.checked ? 300 : 1000);
        };

        ws.onclose = stopBroadcast;
        ws.onerror = err => console.error("WebSocket Error:", err);

    } catch (err) {
        console.error(err);
        alert("Media access denied or stream failed.");
        startBtn.disabled = false;
    }
}

// 🛑 Stop Broadcast
function stopBroadcast() {
    stopBtn.disabled = true;
    startBtn.disabled = false;

    if (recorder && recorder.state !== "inactive") recorder.stop();
    if (finalStream) finalStream.getTracks().forEach(t => t.stop());
    if (ws && ws.readyState === WebSocket.OPEN) ws.close();

    preview.srcObject = null;
    console.log("🛑 Broadcast stopped");
}

startBtn.onclick = startBroadcast;
stopBtn.onclick = stopBroadcast;
</script>
</body>
</html>