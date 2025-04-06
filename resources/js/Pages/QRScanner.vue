<template>
    <div class="qr-scanner">
      <p class="error">{{ error }}</p>
      <div class="qr-scanner-video">
        <p class="error" v-if="invalidQrMessage">{{ invalidQrMessage }}</p>
        <qrcode-stream
          :constraints="selectedConstraints"
          :track="trackFunctionSelected.value"
          @error="onError"
          @detect="onDetect"
          @camera-on="onCameraReady"
        />
      </div>
      <p class="output"><span class="scanned-code" v-html="displayedResult"></span></p>
    </div>
    <div v-if="showAlreadyScannedModal" class="modal-overlay">
      <div class="modal-content">
        <p>Already Scanned</p>
        <button @click="closeModal">Close</button>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { QrcodeStream } from 'vue-qrcode-reader'
import axios from 'axios'

const result = ref('')
const parsedResult = ref('')
const displayedResult = ref('')
const isScanning = ref(true) 
const invalidQrMessage = ref('')
const showAlreadyScannedModal = ref(false)
const scanSound = new Audio('/build/assets/scan-sound.mp3')


function closeModal() {
  showAlreadyScannedModal.value = false
}

function typewriterEffect(text) {
  displayedResult.value = ''
  let index = 0
  const interval = setInterval(() => {
    if (index < text.length) {
      displayedResult.value += text[index]
      index++
    } else {
      clearInterval(interval)
    }
  }, 40) 
}

async function onDetect(detectedCodes) {
  console.log(detectedCodes)
  scanSound.play()
  const codes = detectedCodes.map((code) => code.rawValue)
  result.value = JSON.stringify(codes)
  
  if (codes.length === 0) {
    parsedResult.value = ''
    return
  }

  try {
    // Send the scanned QR code to the server for validation
    const response = await axios.post('/attendance/scan', { qr_data: codes[0] })
    if (response.data.student) {
      const { name, year, course, status } = response.data.student
      let courseAcronym = ''
      
      if (course === 'None') {
        parsedResult.value = `<span style="color: ${status === 'Checked In' ? 'green' : 'red'};">${status}: </span> ${name} ${year}`
      } else {
        switch (course) {
          case 'Bachelor of Science in Agriculture':
            courseAcronym = 'BSA'
            break
          case 'Bachelor of Elementary Education':
            courseAcronym = 'BEEd'
            break
          case 'Bachelor of Secondary Education':
            courseAcronym = 'BSEd'
            break
          case 'Bachelor of Science in Information Technology':
            courseAcronym = 'BSIT'
            break
          case 'Bachelor of Science in Hospitality Management':
            courseAcronym = 'BSHM'
            break
          default:
            courseAcronym = course // Fallback to full course name if no match
        }
        parsedResult.value = `<span style="color: ${status === 'Checked In' ? 'green' : 'red'};">${status}: </span> ${name} ${year} - ${courseAcronym}`
      }
      invalidQrMessage.value = ''
      typewriterEffect(parsedResult.value) // Use typewriter effect
    } else if (response.data.invalid) { 
      invalidQrMessage.value = 'Invalid QR Code'
      parsedResult.value = ''
      displayedResult.value = '' // Clear displayed result

      // Set timeout to clear the invalid QR code message after 4 seconds
      setTimeout(() => {
        invalidQrMessage.value = ''
      }, 4000)
    } else {
      invalidQrMessage.value = 'Invalid QR Code'
      parsedResult.value = ''
      displayedResult.value = '' // Clear displayed result

      // Set timeout to clear the invalid QR code message after 4 seconds
      setTimeout(() => {
        invalidQrMessage.value = ''
      }, 4000)
    }
  } catch (e) {
    console.error('Failed to process QR code', e)
    invalidQrMessage.value = 'An error occurred while processing the QR code'

    // Set timeout to clear the error message after 4 seconds
    setTimeout(() => {
      invalidQrMessage.value = ''
    }, 4000)
  }
}

const selectedConstraints = ref({ facingMode: 'environment' })
const defaultConstraintOptions = [
  { label: 'rear camera', constraints: { facingMode: 'environment' } },
  { label: 'front camera', constraints: { facingMode: 'user' } }
]
const constraintOptions = ref(defaultConstraintOptions)

async function onCameraReady() {
  const devices = await navigator.mediaDevices.enumerateDevices()
  const videoDevices = devices.filter(({ kind }) => kind === 'videoinput')

  constraintOptions.value = [
    ...defaultConstraintOptions,
    ...videoDevices.map(({ deviceId, label }) => ({
      label: `${label} (ID: ${deviceId})`,
      constraints: { deviceId }
    }))
  ]

  error.value = ''
}

function paintOutline(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const [firstPoint, ...otherPoints] = detectedCode.cornerPoints

    ctx.strokeStyle = 'red'

    ctx.beginPath()
    ctx.moveTo(firstPoint.x, firstPoint.y)
    for (const { x, y } of otherPoints) {
      ctx.lineTo(x, y)
    }
    ctx.lineTo(firstPoint.x, firstPoint.y)
    ctx.closePath()
    ctx.stroke()
  }
}

function paintBoundingBox(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const {
      boundingBox: { x, y, width, height }
    } = detectedCode

    ctx.lineWidth = 2
    ctx.strokeStyle = '#007bff'
    ctx.strokeRect(x, y, width, height)
  }
}

function paintCenterText(detectedCodes, ctx) {
  for (const detectedCode of detectedCodes) {
    const { boundingBox, rawValue } = detectedCode

    const centerX = boundingBox.x + boundingBox.width / 2
    const centerY = boundingBox.y + boundingBox.height / 2

    const fontSize = Math.max(12, (50 * boundingBox.width) / ctx.canvas.width)

    ctx.font = `bold ${fontSize}px sans-serif`
    ctx.textAlign = 'center'

    ctx.lineWidth = 3
    ctx.strokeStyle = '#35495e'
    ctx.strokeText(detectedCode.rawValue, centerX, centerY)

    ctx.fillStyle = '#5cb984'
    ctx.fillText(rawValue, centerX, centerY)
  }
}

const trackFunctionOptions = [
  { text: 'nothing (default)', value: undefined },
  { text: 'outline', value: paintOutline },
  { text: 'centered text', value: paintCenterText },
  { text: 'bounding box', value: paintBoundingBox }
]
const trackFunctionSelected = ref(trackFunctionOptions[1])

const error = ref('')

function onError(err) {
  error.value = `[${err.name}]: `

  if (err.name === 'NotAllowedError') {
    error.value += 'you need to grant camera access permission'
  } else if (err.name === 'NotFoundError') {
    error.value += 'no camera on this device'
  } else if (err.name === 'NotSupportedError') {
    error.value += 'secure context required (HTTPS, localhost)'
  } else if (err.name === 'NotReadableError') {
    error.value += 'is the camera already in use?'
  } else if (err.name === 'OverconstrainedError') {
    error.value += 'installed cameras are not suitable'
  } else if (err.name === 'StreamApiNotSupportedError') {
    error.value += 'Stream API is not supported in this browser'
  } else if (err.name === 'InsecureContextError') {
    error.value +=
      'Camera access is only permitted in secure context. Use HTTPS or localhost rather than HTTP.'
  } else {
    error.value += err.message
  }

  error.value += ` (Details: ${JSON.stringify(err)})`
}
</script>

<style scoped>
.qr-scanner {
  text-align: center;
}

.qr-scanner-video {
  width: 400px;
  height: 250px;
  margin: -5px auto 7px auto;
}

.output {
  font-size: 16px;
  font-weight: bold;
  color: #38a169; /*For In*/
  /* color:#ff1a1a; */ /*For Out*/
  margin-top: 12px;
  text-align: left;
  margin-left: 15px;
}
.scanned-code{
  font-size: 16px;
  color: #007bff;
  font-weight: bold;
  margin-left:2px;
  display: inline-block;
  white-space: pre-wrap; /* Ensure proper spacing for typewriter effect */
}

.scan-button {
  margin-left: 44%;
  padding: 8px 15px;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.scan-button.start {
  background-color: #28a745;
  margin-top: 20px;
}

.scan-button.start:hover {
  background-color: #218838;
}

.scan-button.stop {
  background-color: #dc3545;
}

.scan-button.stop:hover {
  background-color: #c82333;
}

.error {
  text-align: center;
  font-weight: bold;
  color: red;
  margin-bottom: 5px;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  text-align: center;
}

.modal-content p {
  font-size: 18px;
  margin-bottom: 20px;
}

.modal-content button {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  background-color: #007bff;
  color: white;
  cursor: pointer;
  font-size: 16px;
}

.modal-content button:hover {
  background-color: #0056b3;
}

.latest-scanned {
  font-size: 16px;
  color: #007bff;
  margin-top: 10px;
}
</style>