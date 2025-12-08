/**
 * QR Code Scanner Module v2
 * Safe version - Only uses confirmed working APIs
 * Manual zoom support (pinch/scroll)
 */

const QRCodeScanner = (() => {
    'use strict';

    let html5QrcodeScanner = null;
    let currentZoom = 1;
    let maxZoom = 5; // Will be updated based on device capabilities

    /**
     * Get optimized scanner configuration
     */
    function getOptimizedConfig() {
        return {
            fps: 20, // Increased from 15 for better detection
            aspectRatio: 4/3,
            disableFlip: false, // Important: allows detecting flipped QR codes
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                // Dynamic sizing for better small QR detection
                const minDimension = Math.min(viewfinderWidth, viewfinderHeight);
                return {
                    width: Math.floor(minDimension * 0.85), // 85% instead of 80% for larger detection area
                    height: Math.floor(minDimension * 0.85)
                };
            }
        };
    }

    /**
     * Auto-rotate video based on device orientation
     */
    function enableAutoRotation() {
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                // Html5Qrcode handles rotation automatically
                // This just logs it for debugging
                console.log('Device rotated, scanner adapting...');
            }, 100);
        });
    }

    /**
     * Initialize the QR code scanner
     */
    function init(scannerId = 'scanner') {
        if (html5QrcodeScanner) {
            console.warn('QRCodeScanner already initialized');
            return html5QrcodeScanner;
        }

        if (typeof Html5Qrcode === 'undefined') {
            throw new Error('Html5Qrcode library not loaded');
        }

        html5QrcodeScanner = new Html5Qrcode(scannerId);
        
        // Enable auto-rotation on device orientation change
        enableAutoRotation();

        // Note: Removed enableZoomControls() - zoom only via buttons now
        
        return html5QrcodeScanner;
    }

    /**
     * Start scanner with a specific device
     */
    function startScannerWithDevice(deviceId, onSuccess, onError) {
        if (!html5QrcodeScanner) {
            console.error('Scanner not initialized');
            return;
        }

        const config = getOptimizedConfig();

        html5QrcodeScanner.start(deviceId, config,
            (decodedText) => {
                if (typeof onSuccess === 'function') {
                    onSuccess(decodedText);
                }
                
                // Pause to prevent rapid re-scanning
                try {
                    html5QrcodeScanner.pause();
                    setTimeout(() => {
                        if (html5QrcodeScanner) {
                            html5QrcodeScanner.resume().catch(() => {
                                // Resume failed, scanner might be stopped
                            });
                        }
                    }, 700);
                } catch (e) {
                    // Ignore pause/resume errors
                }
            },
            (errorMessage) => {
                // Ignore decode errors
            }
        )
        .then(() => {
            const statusElement = document.getElementById('scanner-status');
            if (statusElement) {
                statusElement.innerHTML = 
                    '<i class="fas fa-check text-success"></i> Scanner Active ' +
                    '<small style="display: block; margin-top: 4px; color: #999; font-size: 0.85em;">' +
                    'Use zoom buttons below to adjust camera zoom' +
                    '</small>';
            }
        })
        .catch(err => {
            console.error('Scanner error:', err);
            if (typeof onError === 'function') {
                onError(err);
            } else {
                const statusElement = document.getElementById('scanner-status');
                if (statusElement) {
                    statusElement.innerHTML = 
                        '<div style="color: #d9534f;">' +
                        '<i class="fas fa-times"></i> ' + 
                        (err.message || 'Scanner error') + 
                        '</div>';
                }
            }
        });
    }

    /**
     * Get list of available cameras
     */
    function getCameras() {
        return new Promise((resolve, reject) => {
            if (!Html5Qrcode.getCameras) {
                reject(new Error('getCameras not supported'));
                return;
            }

            Html5Qrcode.getCameras()
                .then(cameras => resolve(cameras || []))
                .catch(err => reject(err));
        });
    }

    /**
     * Stop the scanner
     */
    function stop() {
        return new Promise((resolve) => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop()
                    .then(() => {
                        currentZoom = 1;
                        resolve();
                    })
                    .catch(() => {
                        currentZoom = 1;
                        resolve();
                    });
            } else {
                resolve();
            }
        });
    }

    /**
     * Pause the scanner
     */
    function pause() {
        return new Promise((resolve) => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause()
                    .then(() => resolve())
                    .catch(() => resolve());
            } else {
                resolve();
            }
        });
    }

    /**
     * Resume the scanner
     */
    function resume() {
        return new Promise((resolve) => {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.resume()
                    .then(() => resolve())
                    .catch(() => resolve());
            } else {
                resolve();
            }
        });
    }

    /**
     * Zoom in (increase zoom)
     */
    function zoomIn() {
        currentZoom = Math.min(maxZoom, currentZoom + 0.5);
        applyZoom();
    }

    /**
     * Zoom out (decrease zoom)
     */
    function zoomOut() {
        currentZoom = Math.max(1, currentZoom - 0.5);
        applyZoom();
    }

    /**
     * Apply current zoom level
     */
    function applyZoom() {
        try {
            if (html5QrcodeScanner && typeof html5QrcodeScanner.applyVideoConstraints === 'function') {
                html5QrcodeScanner.applyVideoConstraints({ zoom: currentZoom }).catch(() => {
                    // Silently ignore if zoom not supported
                });
            }
        } catch (e) {
            // Zoom not supported on this device
        }
        
        // Update zoom display if exists
        const zoomDisplay = document.getElementById('zoom-level-display');
        if (zoomDisplay) {
            zoomDisplay.textContent = currentZoom.toFixed(1) + 'x';
        }
    }

    // Public API
    return {
        init,
        startScannerWithDevice,
        getCameras,
        stop,
        pause,
        resume,
        getScanner: () => html5QrcodeScanner,
        getCurrentZoom: () => currentZoom,
        zoomIn,
        zoomOut
    };
})();
