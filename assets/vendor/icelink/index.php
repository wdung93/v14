<html>
<head>
    <title>IceLink Conference - WebRTC</title>
    <script type="text/javascript" src="Scripts/fm.js"></script>
    <script type="text/javascript" src="Scripts/fm.websync.js"></script>
    <script type="text/javascript" src="Scripts/fm.websync.subscribers.js"></script>
    <script type="text/javascript" src="Scripts/fm.websync.chat.js"></script>
    <script type="text/javascript" src="Scripts/fm.icelink.js"></script>
    <script type="text/javascript" src="Scripts/fm.icelink.webrtc.js"></script>
    <script type="text/javascript" src="Scripts/fm.icelink.websync.js"></script>
    <script type="text/javascript" src="app.js"></script>
    <script type="text/javascript" src="localMedia.js"></script>
    <script type="text/javascript" src="signalling.js"></script>
    <script type="text/javascript">
        fm.util.addOnLoad(function ()
        {
            // Get DOM elements.
            var sessionSelector = document.getElementById('sessionSelector');
            var createSessionLabel = document.getElementById('createSessionLabel');
            var joinSessionTextBox = document.getElementById('joinSessionTextBox');
            var createSessionButton = document.getElementById('createSessionButton');
            var joinSessionButton = document.getElementById('joinSessionButton');
            var captureScreenCheckbox = document.getElementById('captureScreenCheckbox');

            var videoChat = document.getElementById('videoChat');
            var loading = document.getElementById('loading');
            var video = document.getElementById('video');

            var log = document.getElementById('log');
            var leaveButton = document.getElementById('leaveButton');
            var toggleAudioMute = document.getElementById('toggleAudioMute');
            var toggleVideoMute = document.getElementById('toggleVideoMute');
            var chromeExtensionInstallButton = document.getElementById('chromeExtensionInstallButton');
            var firefoxExtensionInstallButton = document.getElementById('firefoxExtensionInstallButton');

            // Create new App.
            var app = new App(log);
            
            // Create a random 6 digit number for the new session ID.
            createSessionLabel.innerHTML = Math.floor(Math.random() * 900000) + 100000;

            var startSignalling = function()
            {
                app.startSignalling(function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                    else
                    {
                        // Stop signalling when the page unloads.
                        fm.util.observe(window, 'unload', function()
                        {
                            stopSignalling();
                        });
                    }
                });
            };

            var stopSignalling = function()
            {
                app.stopSignalling(function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                });
            };

            var startLocalMedia = function()
            {
                app.startLocalMedia(video, captureScreenCheckbox.checked, function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                    else
                    {
                        // Enable the media controls.
                        toggleAudioMute.removeAttribute('disabled');
                        toggleVideoMute.removeAttribute('disabled');

                        // Stop local media when the page unloads.
                        fm.util.observe(window, 'unload', function()
                        {
                            stopLocalMedia();
                        });
                        
                        // Hide the loading indicator.
                        loading.style.display = 'none';

                        // Show the video feed(s).
                        video.style.display = 'block';

                        // Start conference now that the local media is available.
                        startConference();
                    }
                });
            };

            var stopLocalMedia = function()
            {
                app.stopLocalMedia(function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                    else
                    {
                        // Disable the media controls.
                        toggleAudioMute.setAttribute('disabled', 'disabled');
                        toggleVideoMute.setAttribute('disabled', 'disabled');
                    }
                });
            };

            var startConference = function()
            {
                app.startConference(function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                    else
                    {
                        // Enable the leave button.
                        leaveButton.removeAttribute('disabled');

                        // Stop conference when the page unloads.
                        fm.util.observe(window, 'unload', function()
                        {
                            stopConference();
                        });
                    }
                });
            };

            var stopConference = function()
            {
                app.stopConference(function(error)
                {
                    if (error != null)
                    {
                        alert(error);
                    }
                    else
                    {
                        // Disable the leave button.
                        leaveButton.setAttribute('disabled', 'disabled');
                    }
                });
            };

            var switchToVideoChat = function(sessionId)
            {
                if (sessionId.length == 6)
                {
                    app.sessionId = sessionId;

                    fm.log.info('Session ID: <span style="font-size: 1.5em;">' + app.sessionId + '</span>');

                    location.hash = app.sessionId + '&screen=' + (captureScreenCheckbox.checked ? '1' : '0');

                    // Show the video chat.
                    videoChat.style.display = 'block';

                    // Hide the session selector.
                    sessionSelector.style.display = 'none';

                    // Start local media.
                    startLocalMedia();
                }
                else
                {
                    alert('Session ID must be 6 digits long.');
                }
            };

            // Attach DOM events.
            fm.util.observe(createSessionButton, 'click', function(evt)
            {
                switchToVideoChat(createSessionLabel.innerHTML);
            });
            fm.util.observe(joinSessionButton, 'click', function(evt)
            {
                switchToVideoChat(joinSessionTextBox.value);
            });
            fm.util.observe(joinSessionTextBox, 'keydown', function(evt)
            {
                // Treat Enter as button click.
                var charCode = (evt.which) ? evt.which : event.keyCode;
                if (charCode == 13)
                {
                    switchToVideoChat(joinSessionTextBox.value);
                    return false;
                }
            });
            fm.util.observe(leaveButton, 'click', function(evt)
            {
                stopLocalMedia();
                stopConference();

                // Show the session selector.
                sessionSelector.style.display = 'block';

                // Hide the video chat.
                videoChat.style.display = 'none';
                
                location.hash = '';
            });
            fm.util.observe(toggleAudioMute, 'click', function(evt)
            {
                var muted = app.toggleAudioMute();
                toggleAudioMute.innerHTML = (muted ? 'Unmute' : 'Mute') + ' My Audio';
            });
            fm.util.observe(toggleVideoMute, 'click', function(evt)
            {
                var muted = app.toggleVideoMute();
                toggleVideoMute.innerHTML = (muted ? 'Unmute' : 'Mute') + ' My Video';
            });
            fm.util.observe(chromeExtensionInstallButton, 'click', function()
            {
                if (fm.icelink.webrtc.chromeExtensionRequiresUserGesture)
                {
                    // Try inline install.
                    chrome.webstore.install(fm.icelink.webrtc.localMediaStream.chromeExtensionUrl, function()
                    {
                        location.reload();
                    },
                    function(error)
                    {
                        // Worst case scenario prompt to install manually.
                        if (confirm('Inline installation failed. ' + error + '\n\nOpen Chrome Web Store?'))
                        {
                            window.open(fm.icelink.webrtc.localMediaStream.chromeExtensionUrl, '_blank');
                        }
                    });
                }
                else
                {
                    // Manual installation required.
                    window.open(fm.icelink.webrtc.localMediaStream.chromeExtensionUrl, '_blank');
                }
            });
            fm.util.observe(firefoxExtensionInstallButton, 'click', function()
            {
                // Manual installation required.
                window.open('https://addons.mozilla.org/firefox/downloads/file/332920/icelink_webrtc_screen_capture-2.7.2-fx.xpi', '_blank');
            });
            
            // Ready? Start signalling when the page loads.
            startSignalling();

            // Automatically join if the session ID is in the URL.
            var hash = location.href.split("#")[1];
            if (hash)
            {
                var sessionId = hash.split('&')[0];
                var screen = (hash.split('&')[1] == 'screen=1');
                if (screen)
                {
                    captureScreenCheckbox.checked = 'checked';
                }
                if (sessionId)
                {
                    joinSessionTextBox.value = sessionId;
                    switchToVideoChat(sessionId);
                }
            }
        });

        var isNumeric = function(evt)
        {
            // Only accept digit- and control-keys.
            var charCode = (evt.which) ? evt.which : event.keyCode;
            return (charCode <= 31 || (charCode >= 48 && charCode <= 57));
        };
    </script>
    <style type="text/css">
        *
        {
            font-family: sans-serif;
            font-size: 10pt;
            -moz-box-sizing: border-box;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
        }
        #sessionSelector
        {
            float: left;
            width: 66%;
            height: 100%;
            padding: 1em;
        }
        #sessionSelector table
        {
            margin: auto;
            text-align: center;
        }
        #videoChat
        {
            float: left;
            width: 67%;
            height: 100%;
            display: none;
        }
        #log
        {
            float: right;
            width: 33%;
            height: 100%;
            padding: 1em;
            background-color: #eee;
        }
        #loading
        {
            width: 100%;
            height: 100%;
            background: url(spiffygif_128x128.gif) no-repeat center;
        }
        #video
        {
            background-color: #000;
            width: 100%;
            height: 100%;
            position: relative;
            display: none;
        }
        #createSessionLabel
        {
            font-size: 1.5em;
        }
    </style>
</head>
<body style="padding: 0; margin: 0; overflow: auto;">
    <div id="sessionSelector">
        <table>
            <tr>
                <td>Create Session with the following ID:</td>
                <td></td>
                <td>Join Session with the following ID:</td>
            </tr>
            <tr>
                <td><div id="createSessionLabel"></div></td>
                <td>OR</td>
                <td><input type="text" id="joinSessionTextBox" maxlength="6" onkeypress="return isNumeric(event)" /></td>
            </tr>
            <tr>
                <td><button id="createSessionButton">Create</button></td>
                <td></td>
                <td><button id="joinSessionButton">Join</button></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="checkbox" id="captureScreenCheckbox" /> Capture Screen</td>
                <td></td>
            </tr>
        </table>
    </div>
    <div id="videoChat">
        <div id="loading"></div>
        <div id="video"></div>
    </div>
    <div id="log">
        <p style="float: left;">
            <button id="leaveButton" disabled="disabled">Leave</button>
            <button id="chromeExtensionInstallButton" style="display: none">Install Screen-Sharing Extension</button>
            <button id="firefoxExtensionInstallButton" style="display: none">Install Screen-Sharing Extension</button>
        </p>
        <p style="float: right;">
            <button id="toggleAudioMute" disabled="disabled">Mute My Audio</button>
            <button id="toggleVideoMute" disabled="disabled">Mute My Video</button>
        </p>
        <p style="clear: both;"><b>Log</b></p>
    </div>
</body>
</html>