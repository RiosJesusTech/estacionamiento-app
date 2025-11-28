from flask import Flask, Response, jsonify
from ultralytics import YOLO
import cv2
from datetime import datetime
from flask_cors import CORS

app = Flask(__name__)
CORS(app)
model = YOLO('yolov8n.pt')
clases_vehiculos = [2, 3, 5, 7]  # Clases: car, motorcycle, bus, truck

ultima_deteccion = None
ultimo_tipo = None

def mapear_tipo(cls):
    if cls == 2:  # car
        return 'automovil'
    elif cls == 3:  # motorcycle
        return 'motocicleta'
    elif cls in [5, 7]:  # bus, truck
        return 'otros'
    return None

def generate_frames():
    global ultima_deteccion, ultimo_tipo
    cap = cv2.VideoCapture(1)  # Asegúrate de que la cámara esté disponible

    while True:
        success, frame = cap.read()
        if not success:
            break

        results = model.predict(source=frame, conf=0.5, classes=clases_vehiculos, stream=True)

        for result in results:
            for box in result.boxes:
                x1, y1, x2, y2 = map(int, box.xyxy[0])
                conf = box.conf[0]
                cls = int(box.cls[0])

                if cls in clases_vehiculos:
                    label = model.names[cls]
                    tipo_mapeado = mapear_tipo(cls)

                    cv2.rectangle(frame, (x1, y1), (x2, y2), (0, 255, 0), 2)
                    cv2.putText(frame, f'{label} {conf:.2f}', (x1, y1 - 10),
                                cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2)

                    if tipo_mapeado != ultima_deteccion:
                        ahora = datetime.now().strftime('%Y-%m-%d %H:%M:%S')
                        with open('registro_vehiculos.txt', 'a') as registro:
                            registro.write(f'{ahora} - {tipo_mapeado}\n')
                        ultima_deteccion = tipo_mapeado
                        ultimo_tipo = tipo_mapeado

        # Convertir a JPEG
        ret, buffer = cv2.imencode('.jpg', frame)
        frame_bytes = buffer.tobytes()

        yield (b'--frame\r\n'
               b'Content-Type: image/jpeg\r\n\r\n' + frame_bytes + b'\r\n')

    cap.release()

@app.route('/video_feed')
def video_feed():
    return Response(generate_frames(),
                    mimetype='multipart/x-mixed-replace; boundary=frame')

@app.route('/last_detection')
def last_detection():
    return jsonify({
        'tipo': ultimo_tipo
    })

@app.route('/capture')
def capture():
    cap = cv2.VideoCapture(1)
    success, frame = cap.read()
    cap.release()
    if success:
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        filename = f'captura_{timestamp}.jpg'
        cv2.imwrite(filename, frame)
        return jsonify({'message': 'Foto capturada', 'filename': filename})
    else:
        return jsonify({'error': 'No se pudo capturar la foto'}), 500

if __name__ == '__main__':
    app.run(debug=True)

