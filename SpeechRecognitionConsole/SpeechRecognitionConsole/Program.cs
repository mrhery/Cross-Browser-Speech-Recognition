using System;
using System.Globalization;
using System.Speech.Recognition;
using System.IO;
using Newtonsoft.Json;
using System.Collections.Generic;
using System.Speech.AudioFormat;
using System.Threading;

namespace SpeechRecognitionConsole
{
    class Program
    {
        static SpeechRecognitionEngine sre;
        static int sec = 0;
        static void Main(string[] args)
        {
            bool run = true;
            var libraryPath = args[1].ToString();
            try
            {
                if (!File.Exists(libraryPath))
                {
                    File.WriteAllText(libraryPath, "[]");
                }

                List<string> library = JsonConvert.DeserializeObject<List<string>>(File.ReadAllText(libraryPath));
                CultureInfo ci = new CultureInfo("en-US");

                sre = new SpeechRecognitionEngine(ci);
                var filePath = args[0].ToString();
                if (File.Exists(filePath))
                {
                    sre.SetInputToAudioStream(
                        File.OpenRead(filePath),
                        new SpeechAudioFormatInfo(44100, AudioBitsPerSample.Sixteen, AudioChannel.Mono)
                    );

                    Choices commands = new Choices();
                    foreach (string lib in library)
                    {
                        commands.Add(lib);
                    }

                    GrammarBuilder grammar = new GrammarBuilder();
                    grammar.Append(commands);
                    Grammar gcommands = new Grammar(grammar);

                    sre.LoadGrammarAsync(gcommands);
                    sre.RecognizeAsync(RecognizeMode.Single);
                    sre.SpeechRecognized += delegate (object sender, SpeechRecognizedEventArgs e)
                    {
                        if (e.Result.Confidence > 0.75)
                        {
                            var result = e.Result.Text;
                            Console.WriteLine(result);
                        }

                        run = false;
                    };
                }
                else
                {
                    run = false;
                }
            }
            catch (Exception ex)
            {
                run = false;
            }

            while (run)
            {
                Thread.Sleep(1000);

                sec += 1;

                if (sec > 3)
                {
                    run = false;
                }
            }
        }
    }
}
